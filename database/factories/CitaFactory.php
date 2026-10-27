<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cita>
 */
class CitaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => User::factory(),
            'doctor_id' => User::factory(),
            'especialidad' => fake()->randomElement(['Cardiología', 'Pediatría', 'Medicina general']),
            'fecha_hora' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'estado' => 'pendiente',
            'motivo' => fake()->sentence(),
        ];
    }
}
