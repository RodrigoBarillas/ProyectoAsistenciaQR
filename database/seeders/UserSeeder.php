<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId   = DB::table('roles')->where('nombre', RoleEnum::ADMINISTRADOR->value)->value('id');
        $docenteId = DB::table('roles')->where('nombre', RoleEnum::DOCENTE->value)->value('id');

        $users = [
            [
                'name'              => 'Admin Principal',
                'email'             => 'admin@uped.edu.sv',
                'password'          => Hash::make('password'),
                'role_id'           => $adminId,
                'status'            => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Carlos Mendoza',
                'email'             => 'carlos.mendoza@uped.edu.sv',
                'password'          => Hash::make('password'),
                'role_id'           => $docenteId,
                'status'            => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'María García',
                'email'             => 'maria.garcia@uped.edu.sv',
                'password'          => Hash::make('password'),
                'role_id'           => $docenteId,
                'status'            => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'José Rodríguez',
                'email'             => 'jose.rodriguez@uped.edu.sv',
                'password'          => Hash::make('password'),
                'role_id'           => $docenteId,
                'status'            => false,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                array_merge($user, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
