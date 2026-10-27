<?php

namespace Tests\Feature\Api;

use App\Models\Cita;
use App\Models\User;
use App\Notifications\CitaAgendadaNotification;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CitaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_citas_confirmadas(): void
    {
        $paciente = User::factory()->create();
        $medico = User::factory()->create();

        Cita::factory()->for($paciente, 'patient')->for($medico, 'doctor')->create([
            'estado' => 'confirmada',
        ]);
        Cita::factory()->for($paciente, 'patient')->for($medico, 'doctor')->create([
            'estado' => 'pendiente',
        ]);

        Sanctum::actingAs($paciente);

        $response = $this->getJson('/api/citas?estado=confirmada');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.estado', 'confirmada')
            ->assertJsonPath('0.patient.id', $paciente->id)
            ->assertJsonPath('0.doctor.id', $medico->id);
    }

    public function test_agendar_cita_y_envio_correo(): void
    {
        Notification::fake();

        $paciente = User::factory()->create();
        $otroPaciente = User::factory()->create();
        $medico = User::factory()->create();

        Sanctum::actingAs($paciente);

        $response = $this->postJson('/api/citas', [
            'patient_id' => $otroPaciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Cardiología',
            'fecha_hora' => '2030-05-20 10:30:00',
            'motivo' => 'Control preventivo',
        ]);

        $response->assertCreated()
            ->assertJsonPath('patient_id', $paciente->id)
            ->assertJsonPath('doctor_id', $medico->id)
            ->assertJsonPath('estado', 'pendiente');

        $this->assertDatabaseHas('citas', [
            'id' => $response->json('id'),
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Cardiología',
            'estado' => 'pendiente',
            'motivo' => 'Control preventivo',
        ]);

        Notification::assertSentTo(
            $medico,
            CitaAgendadaNotification::class,
            fn (CitaAgendadaNotification $notification, array $channels): bool => $channels === ['mail']
                && $notification->cita->id === $response->json('id'),
        );
    }

    public function test_detecta_problema_n_mas_1(): void
    {
        $citas = Cita::factory()->count(5)->create();

        $this->assertCount(5, $citas->pluck('patient_id')->unique());
        $this->assertCount(5, $citas->pluck('doctor_id')->unique());

        $paciente = $citas->first()->patient;
        Sanctum::actingAs($paciente);

        $selectQueries = 0;
        DB::listen(function (QueryExecuted $query) use (&$selectQueries): void {
            if (preg_match('/^\s*select\b/i', $query->sql) === 1) {
                $selectQueries++;
            }
        });

        $response = $this->getJson('/api/citas');

        $response->assertOk()
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => [
                    'patient' => ['id', 'name'],
                    'doctor' => ['id', 'name'],
                ],
            ]);

        $this->assertLessThanOrEqual(
            3,
            $selectQueries,
            'La lista debe cargar citas, pacientes y médicos en un máximo de tres consultas SELECT.',
        );
    }

    public function test_api_requiere_autenticacion(): void
    {
        $this->getJson('/api/citas')->assertUnauthorized();
        $this->postJson('/api/citas')->assertUnauthorized();
        $cita = Cita::factory()->create();
        $this->patchJson(route('api.citas.estado', $cita), ['estado' => 'confirmada'])
            ->assertUnauthorized();
    }

    public function test_api_autentica_con_token_personal_de_sanctum(): void
    {
        $usuario = User::factory()->create();
        $token = $usuario->createToken('prueba-api')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/citas')
            ->assertOk();
    }

    public function test_api_permite_confirmar_una_cita_pendiente(): void
    {
        $usuario = User::factory()->create();
        $cita = Cita::factory()->create(['estado' => 'pendiente']);

        Sanctum::actingAs($usuario);

        $this->patchJson(route('api.citas.estado', $cita), ['estado' => 'confirmada'])
            ->assertOk()
            ->assertJsonPath('id', $cita->id)
            ->assertJsonPath('estado', 'confirmada');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'confirmada',
        ]);
    }

    public function test_api_rechaza_cambiar_estado_de_una_cita_que_ya_no_esta_pendiente(): void
    {
        $usuario = User::factory()->create();
        $cita = Cita::factory()->create(['estado' => 'confirmada']);

        Sanctum::actingAs($usuario);

        $this->patchJson(route('api.citas.estado', $cita), ['estado' => 'cancelada'])
            ->assertConflict()
            ->assertJsonPath('message', 'Solo se pueden confirmar o cancelar citas que todavía están pendientes.');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'confirmada',
        ]);
    }

    public function test_api_solo_acepta_estados_finales_para_citas_pendientes(): void
    {
        $usuario = User::factory()->create();
        $cita = Cita::factory()->create(['estado' => 'pendiente']);

        Sanctum::actingAs($usuario);

        $this->patchJson(route('api.citas.estado', $cita), ['estado' => 'pendiente'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'pendiente',
        ]);
    }
}
