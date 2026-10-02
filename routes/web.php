<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('index'));

Route::get('/healthcheck', HealthCheckController::class);

