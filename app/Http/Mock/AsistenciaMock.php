<?php

namespace App\Http\Mock;
abstract class AsistenciaMock
{

    const GENERAR_QR_SUCCESS = [
        "success" => true,
        "data" => [
            "qr_base64" => "eyJ0eXAiOiJKV"
        ]
    ];

    const REGISTRAR_ASISTENCIA_SUCCESS = [
        "success" => true,
        "message" => "Operation successful.",
        "data" => [
            "estudiante_id" => 3,
            "fecha_asistencia" => "2026-10-06",
            "hora_entrada" => "11:11:24",
            "observaciones" => "Llegó a las 11:11:24. A tiempo."
        ]

    ];
}
