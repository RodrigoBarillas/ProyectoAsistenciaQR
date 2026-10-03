<?php

namespace App\Http\Controllers;

use Laravel\Swagger\Attributes\SwaggerGlobal;

#[SwaggerGlobal(['security' => 'bearerAuth', 'middleware' => 'auth'])]
abstract class Controller
{
    //
}
