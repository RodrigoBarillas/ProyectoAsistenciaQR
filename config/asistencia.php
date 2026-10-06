<?php

return [
    'zona_horaria'       => env('ASISTENCIA_ZONA_HORARIA', 'America/El_Salvador'),
    'hora_inicio'        => env('ASISTENCIA_HORA_INICIO', '07:00'),
    'tolerancia_minutos' => (int) env('ASISTENCIA_TOLERANCIA_MINUTOS', 15),
];