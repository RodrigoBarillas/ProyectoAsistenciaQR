<?php

namespace App\Http\Mock;


abstract class RoleMock
{

    const ROLES_INDEX = [
        "success" => true,
        "message" => "Roles retrieved successfully.",
        "data" => [
            [
                "id" => 1,
                "name" => "admin",
                "guard_name" => "api",
                "permissions" => [
                    ["id" => 1, "name" => "role.view"],
                    ["id" => 2, "name" => "role.assign"],
                ]
            ],
            [
                "id" => 2,
                "name" => "teacher",
                "guard_name" => "api",
                "permissions" => [
                    ["id" => 1, "name" => "role.view"],
                ]
            ]
        ]
    ];


    const ROLE_SHOW = [
        "success" => true,
        "message" => "Role retrieved successfully.",
        "data" => [
            "id" => 1,
            "name" => "admin",
            "guard_name" => "api",
            "permissions" => [
                ["id" => 1, "name" => "role.view"],
                ["id" => 2, "name" => "role.assign"],
            ]
        ]
    ];


    const ROLE_STORE = [
        "success" => true,
        "message" => "Role created successfully.",
        "data" => [
            "id" => 3,
            "name" => "student",
            "guard_name" => "api",
            "permissions" => []
        ]
    ];


    const ROLE_UPDATE = [
        "success" => true,
        "message" => "Role updated successfully.",
        "data" => [
            "id" => 3,
            "name" => "student",
            "guard_name" => "api",
            "permissions" => [
                ["id" => 1, "name" => "role.view"],
            ]
        ]
    ];


    const ROLE_DESTROY = [
        "success" => true,
        "message" => "Role deleted successfully.",
        "data" => []
    ];
}
