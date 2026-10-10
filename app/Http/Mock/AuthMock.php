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
                'token_type' => 'bearer',
                'refresh_token' => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
                "expires_in" => 3600,
                "role" => "Administrador"
            ]
        ]
    ];


    const REFRESH_SUCCESS = [
        "success" => true,
        "message" => "Token refreshed successfully.",
        "data" => [
            "token" => [
                "access_token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
                'token_type' => 'bearer',
                'refresh_token' => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
                "expires_in" => 3600,
                "role" => "Administrador"
            ]
        ]
    ];


    const LOGOUT_SUCCESS = [
        "success" => true,
        "message" => "Successfully logged out.",
        "data" => []
    ];

    const ME_SUCCESS_ALUMNO = [
        "success" => true,
        "message" => "Perfil obtenido correctamente.",
        "data" => [
            "id" => 5,
            "name" => "Luis Fernando Hernández Cruz",
            "email" => "luis.hernandez@uped.edu.sv",
            "role" => "Alumno",
            "estudiante" => [
                "id" => 2,
                "codigo_estudiante" => "EST-002",
                "nombres" => "Luis Fernando",
                "apellidos" => "Hernández Cruz",
                "qr_token" => "550e8400-e29b-41d4-a716-446655440000",
                "seccion_id" => 1,
                "seccion" => [
                    "id" => 1,
                    "nombre" => "A",
                    "grado_id" => 1,
                    "grado" => [
                        "id" => 1,
                        "nombre" => "Primero Básico",
                        "estado" => true,
                        "created_at" => "2026-01-10T15:00:00.000000Z",
                        "updated_at" => "2026-01-10T15:00:00.000000Z",
                    ],
                    "estado" => true,
                    "created_at" => "2026-01-10T15:00:00.000000Z",
                    "updated_at" => "2026-01-10T15:00:00.000000Z",
                ],
                "estado" => true,
                "created_at" => "2026-01-10T15:00:00.000000Z",
                "updated_at" => "2026-01-10T15:00:00.000000Z",
            ],
        ],
    ];

    const ME_SUCCESS_DOCENTE = [
        "success" => true,
        "message" => "Perfil obtenido correctamente.",
        "data" => [
            "id" => 2,
            "name" => "Carlos Mendoza",
            "email" => "carlos.mendoza@uped.edu.sv",
            "role" => "Docente",
            "estudiante" => null,
        ],
    ];
}
