<?php

use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\SeccionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Authentication ────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {

        Route::post('login', [AuthController::class, 'login'])->name('login');

        Route::middleware('auth')->group(function () {
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('logout',  [AuthController::class, 'logout'])->name('logout');
        });
    });

    // ── Protected ─────────────────────────────────────────────────────────────
    Route::middleware('auth')->group(function () {

        // ── Permissions ───────────────────────────────────────────────────────
        Route::prefix('permissions')->group(function () {

            Route::get('/', [PermissionController::class, 'index'])
                ->middleware('permission:role.view')
                ->name('index');
        });

        // ── Roles ─────────────────────────────────────────────────────────────
        Route::prefix('roles')->group(function () {

            Route::get('/', [RoleController::class, 'index'])
                ->middleware('permission:role.view')
                ->name('index');

            Route::get('/{role}', [RoleController::class, 'show'])
                ->middleware('permission:role.view')
                ->name('show');

            Route::post('/', [RoleController::class, 'store'])
                ->middleware('permission:role.assign')
                ->name('store');

            Route::put('/{role}', [RoleController::class, 'update'])
                ->middleware('permission:role.assign')
                ->name('update');

            Route::delete('/{role}', [RoleController::class, 'destroy'])
                ->middleware('permission:role.assign')
                ->name('destroy');
        });

        // ── Grados ────────────────────────────────────────────────────────────
        Route::apiResource('grados', GradoController::class);

        // ── Secciones ─────────────────────────────────────────────────────────
        Route::apiResource('secciones', SeccionController::class);
    });
});
