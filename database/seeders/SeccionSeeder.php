<?php

namespace Database\Seeders;

use App\Models\Grado;
use App\Models\Horario;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class SeccionSeeder extends Seeder
{
    public function run(): void
    {
        $horario = Horario::where('nombre', 'Matutino')->first();

        foreach (Grado::all() as $grado) {
            Seccion::updateOrCreate(
                ['grado_id' => $grado->id, 'nombre' => 'A'],
                ['horario_id' => $horario?->id, 'estado' => true],
            );
        }
    }
}
