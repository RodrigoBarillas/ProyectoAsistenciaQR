<?php

namespace App\Services\Role\Contracts;

use App\Http\Requests\Role\RoleRequest;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;

interface RoleServiceInterface
{
    public function index(): RoleCollection;

    public function show(Role $role): RoleResource;

    public function store(RoleRequest $request): RoleResource;

    public function update(RoleRequest $request, Role $role): RoleResource;

    public function destroy(Role $role): void;

    public function findRoleById(mixed $role): Role;
}
