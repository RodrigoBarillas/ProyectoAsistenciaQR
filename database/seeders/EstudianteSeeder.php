<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Estudiante;
use App\Models\Role;
use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('nombre', RoleEnum::ALUMNO->value)->first();

        // Pick the section GradoSeeder/SeccionSeeder created (with an active
        // horario), not just "the first section in the table" — on a DB that
        // already has unrelated manual-testing data, Seccion::first() could
        // land on a section without a horario and silently break the demo
        // QR-attendance flow these seeders are meant to leave ready to try.
        $seccion = Seccion::whereHas('grado', fn ($query) => $query->where('nombre', 'Primero Básico'))
            ->where('nombre', 'A')
            ->first();

        if (! $role || ! $seccion) {
            return;
        }

        $alumnos = [
            ['codigo' => 'EST-001', 'nombres' => 'Ana Lucía', 'apellidos' => 'Pérez López', 'email' => 'ana.perez@uped.edu.sv'],
            ['codigo' => 'EST-002', 'nombres' => 'Luis Fernando', 'apellidos' => 'Hernández Cruz', 'email' => 'luis.hernandez@uped.edu.sv'],
            ['codigo' => 'EST-003', 'nombres' => 'Marta Elena', 'apellidos' => 'Vásquez Ramos', 'email' => 'marta.vasquez@uped.edu.sv'],
        ];

        foreach ($alumnos as $datos) {
            $user = User::updateOrCreate(
                ['email' => $datos['email']],
                [
                    'name'              => "{$datos['nombres']} {$datos['apellidos']}",
                    'password'          => 'password',
                    'role_id'           => $role->id,
                    'status'            => true,
                    'email_verified_at' => now(),
                ],
            );

            $estudiante = Estudiante::firstOrNew(['codigo_estudiante' => $datos['codigo']]);
            $estudiante->fill([
                'nombres'    => $datos['nombres'],
                'apellidos'  => $datos['apellidos'],
                'seccion_id' => $seccion->id,
                'user_id'    => $user->id,
                'estado'     => true,
            ]);

            if (! $estudiante->qr_token) {
                $estudiante->qr_token = Str::uuid()->toString();
            }

            $estudiante->save();
        }
    }
}
