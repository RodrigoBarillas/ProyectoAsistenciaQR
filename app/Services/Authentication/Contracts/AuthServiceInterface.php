<?php

namespace App\Services\Authentication\Contracts;

use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\Authentication\RefreshRequest;
use App\Http\Resources\Authentication\AuthResource;

interface AuthServiceInterface
{
    public function login(LoginRequest $request): AuthResource;

    public function refresh(RefreshRequest $request): AuthResource;

    public function logout(): void;
}
