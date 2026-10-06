<?php

use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\AsistenciaController;
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
        Route::apiResource('grados', GradoController::class)
            ->middlewareFor(['index', 'show'], 'permission:grado.view')
            ->middlewareFor('store', 'permission:grado.create')
            ->middlewareFor('update', 'permission:grado.edit')
            ->middlewareFor('destroy', 'permission:grado.delete');

        // ── Secciones ─────────────────────────────────────────────────────────
        Route::apiResource('secciones', SeccionController::class)
            ->parameters(['secciones' => 'seccion'])
            ->middlewareFor(['index', 'show'], 'permission:seccion.view')
            ->middlewareFor('store', 'permission:seccion.create')
            ->middlewareFor('update', 'permission:seccion.edit')
            ->middlewareFor('destroy', 'permission:seccion.delete');

        // ── Estudiantes ───────────────────────────────────────────────────────
        Route::get('estudiantes/qr/{qr_token}', [EstudianteController::class, 'showByQrToken'])
            ->middleware('permission:estudiante.view');

        Route::apiResource('estudiantes', EstudianteController::class)
            ->middlewareFor(['index', 'show'], 'permission:estudiante.view')
            ->middlewareFor('store', 'permission:estudiante.create')
            ->middlewareFor('update', 'permission:estudiante.edit')
            ->middlewareFor('destroy', 'permission:estudiante.delete');

        // ── Asistencias ───────────────────────────────────────────────────────
        // Teacher/admin generates a QR for a given section.
        Route::get('asistencias/generar-qr/{seccion}', [AsistenciaController::class, 'generarQr'])
            ->middleware('permission:asistencia.mark')
            ->name('asistencias.generar-qr');

        // Authenticated student scans the section QR to mark their own attendance.
        Route::post('asistencias/registrar', [AsistenciaController::class, 'registrar'])
            ->middleware('permission:asistencia.mark')
            ->name('asistencias.registrar');
    });
});
