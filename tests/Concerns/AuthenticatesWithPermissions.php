<?php

namespace Tests\Concerns;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

trait AuthenticatesWithPermissions
{
    /**
     * Create a user whose role has exactly the given permissions, and
     * authenticate as that user on the 'api' (JWT) guard.
     *
     * @param  array<int, string>  $permissions
     */
    protected function actingAsUserWithPermissions(array $permissions): User
    {
        $role = Role::create([
            'nombre' => 'Test Role '.uniqid(),
            'descripcion' => 'Rol generado por los tests de feature',
            'estado' => true,
        ]);

        foreach ($permissions as $permission) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['nombre' => $permission])->id
            );
        }

        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user, 'api');

        return $user;
    }
}
