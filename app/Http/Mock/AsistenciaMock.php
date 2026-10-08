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
        "message" => "Asistencia registrada correctamente.",
        "data" => [
            "id" => 3,
            "estudiante_id" => 3,
            "fecha_asistencia" => "2026-10-06",
            "hora_entrada" => "11:11:24",
            "estado" => "PRESENTE",
            "observaciones" => "Llegó a las 11:11:24. A tiempo.",
            "registrado_por" => 7,
            "created_at" => "2026-10-06T17:11:24.000000Z",
            "updated_at" => "2026-10-06T17:11:24.000000Z",
        ]

    ];

    const HISTORIAL_SUCCESS = [
        "data" => [
            [
                "id" => 3,
                "estudiante_id" => 3,
                "fecha_asistencia" => "2026-10-06",
                "hora_entrada" => "11:11:24",
                "estado" => "PRESENTE",
                "observaciones" => "Llegó a las 11:11:24. A tiempo.",
                "registrado_por" => 7,
                "created_at" => "2026-10-06T17:11:24.000000Z",
                "updated_at" => "2026-10-06T17:11:24.000000Z",
            ],
        ],
        "links" => [
            "first" => "http://localhost:8000/api/v1/asistencias/historial?page=1",
            "last" => "http://localhost:8000/api/v1/asistencias/historial?page=1",
            "prev" => null,
            "next" => null,
        ],
        "meta" => [
            "current_page" => 1,
            "last_page" => 1,
            "per_page" => 15,
            "total" => 1,
        ],
    ];
}
