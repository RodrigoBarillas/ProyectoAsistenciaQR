<?php

namespace App\Services\Permission;

use App\Http\Resources\Permission\PermissionCollection;
use App\Models\Permission;
use App\Services\Permission\Contracts\PermissionServiceInterface;

class PermissionService implements PermissionServiceInterface
{
    public function index(): PermissionCollection
    {
        $permissions = Permission::orderBy('nombre')->get();

        return new PermissionCollection($permissions);
    }
}
