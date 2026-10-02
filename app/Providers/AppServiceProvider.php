<?php

namespace App\Providers;

use App\Services\Authentication\AuthService;
use App\Services\Authentication\Contracts\AuthServiceInterface;
use App\Services\Permission\Contracts\PermissionServiceInterface;
use App\Services\Permission\PermissionService;
use App\Services\Role\Contracts\RoleServiceInterface;
use App\Services\Role\RoleService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
