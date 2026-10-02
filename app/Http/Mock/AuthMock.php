<?php

namespace App\Http\Mock;


abstract class AuthMock
{

    const LOGIN_SUCCESS = [
        "success" => true,
        "message" => "Login successful.",
        "data" => [
            "token" => [
                "access_token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
                "expires_in" => 3600
            ]
        ]
    ];


    const REFRESH_SUCCESS = [
        "success" => true,
        "message" => "Token refreshed successfully.",
        "data" => [
            "token" => [
                "access_token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
                "expires_in" => 3600
            ]
        ]
    ];


    const LOGOUT_SUCCESS = [
        "success" => true,
        "message" => "Successfully logged out.",
        "data" => []
    ];
}
