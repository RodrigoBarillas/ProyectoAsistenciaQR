<?php

namespace Database\Seeders;

use App\Models\Grado;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Primero Básico', 'Segundo Básico'] as $nombre) {
            Grado::updateOrCreate(['nombre' => $nombre], ['estado' => true]);
        }
    }
}
