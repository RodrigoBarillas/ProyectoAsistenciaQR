<?php

namespace App\Http\Mock;


abstract class PermissionMock
{

    const PERMISSIONS_INDEX = [
        "success" => true,
        "message" => "Permissions retrieved successfully.",
        "data" => [
            ["id" => 1, "name" => "role.view",   "guard_name" => "api"],
            ["id" => 2, "name" => "role.assign",  "guard_name" => "api"],
        ]
    ];
}
