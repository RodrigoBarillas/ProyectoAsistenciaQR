<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\Seccion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Estudiante>
 */
class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition(): array
    {
        return [
            'codigo_estudiante' => fake()->unique()->numerify('EST-####'),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'qr_token' => (string) Str::uuid(),
            'seccion_id' => Seccion::factory(),
            'estado' => true,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => false,
        ]);
    }
}
