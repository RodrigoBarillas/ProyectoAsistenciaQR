<?php

namespace App\Services\Role;

use App\Http\Requests\Role\RoleRequest;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use App\Services\Role\Contracts\RoleServiceInterface;
use Illuminate\Support\Facades\DB;

class RoleService implements RoleServiceInterface
{
    public function index(): RoleCollection
    {
        $roles = Role::with('permissions')->orderBy('id')->get();

        return new RoleCollection($roles);
    }

    public function show(Role $role): RoleResource
    {
        $role->load('permissions');

        return new RoleResource($role);
    }

    public function store(RoleRequest $request): RoleResource
    {
        $role = DB::transaction(function () use ($request): Role {
            $role = Role::create([
                'nombre'      => $request->input('nombre'),
                'descripcion' => $request->input('descripcion'),
                'estado'      => $request->boolean('estado', true),
            ]);

            $role->permissions()->sync($request->input('permissions', []));

            return $role;
        });

        $role->load('permissions');

        return new RoleResource($role);
    }

    public function update(RoleRequest $request, Role $role): RoleResource
    {
        DB::transaction(function () use ($request, $role): void {
            $role->update([
                'nombre'      => $request->input('nombre'),
                'descripcion' => $request->input('descripcion'),
                'estado'      => $request->boolean('estado', $role->estado),
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->sync($request->input('permissions', []));
            }
        });

        $role->load('permissions');

        return new RoleResource($role);
    }

    public function destroy(Role $role): void
    {
        DB::transaction(function () use ($role): void {
            $role->permissions()->detach();
            $role->delete();
        });
    }

    public function findRoleById(mixed $role): Role
    {
        if (is_numeric($role)) {
            return Role::findOrFail($role);
        }

        if ($role instanceof Role) {
            return $role;
        }

        throw new \InvalidArgumentException('Invalid role identifier provided.');
    }
}
