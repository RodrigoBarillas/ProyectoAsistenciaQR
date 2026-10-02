<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Upsert all permissions from the enum.
        foreach (PermissionEnum::cases() as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['nombre' => $permission->value],
                [
                    'nombre'      => $permission->value,
                    'descripcion' => $permission->description(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        // 2. Map each role to its permission set and sync the pivot table.
        $rolePermissionMap = [
            RoleEnum::ADMINISTRADOR->value => PermissionEnum::forAdministrador(),
            RoleEnum::DOCENTE->value       => PermissionEnum::forDocente(),
            RoleEnum::ALUMNO->value        => PermissionEnum::forAlumno(),
        ];

        foreach ($rolePermissionMap as $roleName => $permissions) {
            $roleId = DB::table('roles')->where('nombre', $roleName)->value('id');

            if (!$roleId) {
                continue;
            }

            // Clear existing assignments for this role before re-seeding.
            DB::table('role_permission')->where('role_id', $roleId)->delete();

            $rows = [];

            foreach ($permissions as $permission) {
                $permissionId = DB::table('permissions')
                    ->where('nombre', $permission->value)
                    ->value('id');

                if (!$permissionId) {
                    continue;
                }

                $rows[] = [
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                ];
            }

            if (!empty($rows)) {
                DB::table('role_permission')->insert($rows);
            }
        }
    }
}
