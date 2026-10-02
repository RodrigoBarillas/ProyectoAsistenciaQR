<?php

namespace App\Http\Controllers;

use Laravel\Swagger\Attributes\SwaggerGlobal;

#[SwaggerGlobal(['security' => 'bearerToken', 'middleware' => 'auth'])]
abstract class Controller
{
    //
}
