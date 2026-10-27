<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $paciente = User::factory()->create([
            'name' => 'Paciente Demo',
            'email' => 'paciente@example.com',
        ]);

        $medico = User::factory()->create([
            'name' => 'Dra. Andrea Gómez',
            'email' => 'andrea.gomez@example.com',
        ]);

        Cita::factory()->create([
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Medicina general',
            'fecha_hora' => now()->addDays(3),
            'estado' => 'pendiente',
            'motivo' => 'Consulta de control',
        ]);
    }
}
