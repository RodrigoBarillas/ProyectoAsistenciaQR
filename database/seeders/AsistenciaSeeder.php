<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\Estudiante;
use Illuminate\Database\Seeder;

class AsistenciaSeeder extends Seeder
{
    public function run(): void
    {
        $estudiante = Estudiante::where('codigo_estudiante', 'EST-001')->first();

        if (! $estudiante) {
            return;
        }

        Asistencia::updateOrCreate(
            ['estudiante_id' => $estudiante->id, 'fecha_asistencia' => now()->toDateString()],
            [
                'hora_entrada'   => now()->format('H:i:s'),
                'estado'         => 'PRESENTE',
                'observaciones'  => 'Asistencia de ejemplo generada por el seeder.',
                'registrado_por' => null,
            ],
        );
    }
}
