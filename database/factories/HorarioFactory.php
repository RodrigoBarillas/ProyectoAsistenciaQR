<?php

namespace Database\Factories;

use App\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Horario>
 */
class HorarioFactory extends Factory
{
    protected $model = Horario::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->numerify('Horario ####'),
            'estado' => true,
            'tolerancia' => 10,
            'hora_entrada' => '07:00:00',
            'hora_salida' => '12:00:00',
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => false,
        ]);
    }
}
