<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\User;
use App\Notifications\CitaAgendadaNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CitaWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_citas_en_la_vista_web(): void
    {
        $this->withoutVite();

        $paciente = User::factory()->create(['name' => 'Paciente de prueba']);
        $medico = User::factory()->create(['name' => 'Médica de prueba']);

        Cita::factory()->for($paciente, 'patient')->for($medico, 'doctor')->create([
            'especialidad' => 'Pediatría',
            'estado' => 'confirmada',
            'motivo' => 'Consulta de control',
        ]);

        $this->get('/citas')
            ->assertOk()
            ->assertSee('Citas agendadas')
            ->assertSee('Paciente de prueba')
            ->assertSee('Médica de prueba')
            ->assertSee('Pediatría')
            ->assertSee('Confirmada');
    }

    public function test_muestra_el_formulario_de_agendamiento(): void
    {
        $this->withoutVite();

        $this->get('/citas/crear')
            ->assertOk()
            ->assertSee('Agendar una cita')
            ->assertSee('name="patient_id"', false)
            ->assertSee('name="doctor_id"', false)
            ->assertSee('name="fecha_hora"', false);
    }

    public function test_registra_una_cita_desde_el_formulario_web(): void
    {
        Notification::fake();

        $paciente = User::factory()->create();
        $medico = User::factory()->create();

        $this->post('/citas', [
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Medicina general',
            'fecha_hora' => '2030-05-20 10:30:00',
            'motivo' => 'Consulta inicial',
        ])
            ->assertRedirect(route('citas.index'))
            ->assertSessionHas('success', 'Cita agendada correctamente.');

        $this->assertDatabaseHas('citas', [
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'estado' => 'pendiente',
            'motivo' => 'Consulta inicial',
        ]);

        Notification::assertSentTo($medico, CitaAgendadaNotification::class);
    }

    public function test_el_operador_puede_aprobar_una_cita_pendiente_desde_la_lista(): void
    {
        $this->withoutVite();

        $cita = Cita::factory()->create(['estado' => 'pendiente']);

        $this->get(route('citas.index'))
            ->assertOk()
            ->assertSee('Aprobar')
            ->assertSee('Cancelar');

        $this->patch(route('citas.estado', $cita), ['estado' => 'confirmada'])
            ->assertRedirect(route('citas.index'))
            ->assertSessionHas('success', 'La cita quedó confirmada correctamente.');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'confirmada',
        ]);

        $this->get(route('citas.index'))
            ->assertOk()
            ->assertDontSee('Aprobar')
            ->assertSee('Sin acciones disponibles');
    }

    public function test_no_permite_cambiar_el_estado_de_una_cita_ya_confirmada(): void
    {
        $cita = Cita::factory()->create(['estado' => 'confirmada']);

        $this->patch(route('citas.estado', $cita), ['estado' => 'cancelada'])
            ->assertRedirect(route('citas.index'))
            ->assertSessionHas('error', 'Solo se pueden confirmar o cancelar citas que todavía están pendientes.');

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'estado' => 'confirmada',
        ]);
    }
}
