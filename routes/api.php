<?php

use App\Http\Controllers\Authentication\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Authentication ───────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {

        Route::post('login',   [AuthController::class, 'login'])->name('login');

        Route::middleware('auth')->group(function () {
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        });
    });
});
