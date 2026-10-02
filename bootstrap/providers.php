<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    Laravel\Swagger\SwaggerServiceProvider::class,
    PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider::class,


];
