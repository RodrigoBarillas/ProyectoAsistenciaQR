<?php

namespace Database\Seeders;

use App\Models\Horario;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    public function run(): void
    {
        $horarios = [
            [
                'nombre'      => 'Matutino',
                'estado'      => true,
                'tolerancia'  => 10,
                'hora_entrada' => '07:00:00',
                'hora_salida'  => '12:00:00',
            ],
            [
                'nombre'      => 'Vespertino',
                'estado'      => true,
                'tolerancia'  => 10,
                'hora_entrada' => '13:00:00',
                'hora_salida'  => '18:00:00',
            ],
        ];

        foreach ($horarios as $data) {
            Horario::updateOrCreate(['nombre' => $data['nombre']], $data);
        }
    }
}
