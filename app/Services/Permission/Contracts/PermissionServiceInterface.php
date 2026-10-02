<?php

namespace App\Services\Permission\Contracts;

use App\Http\Resources\Permission\PermissionCollection;

interface PermissionServiceInterface
{
    public function index(): PermissionCollection;
}
