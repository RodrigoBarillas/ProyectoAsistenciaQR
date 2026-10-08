<?php

namespace Database\Factories;

use App\Models\Asistencia;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asistencia>
 */
class AsistenciaFactory extends Factory
{
    protected $model = Asistencia::class;

    public function definition(): array
    {
        return [
            'estudiante_id' => Estudiante::factory(),
            // Unique per test run so creating several records for the same
            // student (e.g. a history listing) doesn't collide with the
            // (estudiante_id, fecha_asistencia) unique constraint.
            'fecha_asistencia' => fake()->unique()->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            'hora_entrada' => now()->format('H:i:s'),
            'estado' => 'PRESENTE',
            'observaciones' => null,
            'registrado_por' => null,
        ];
    }

    public function tardia(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'TARDIA',
        ]);
    }

    public function ausente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'AUSENTE',
        ]);
    }
}
