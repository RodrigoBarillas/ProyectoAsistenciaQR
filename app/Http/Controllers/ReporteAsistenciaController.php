<?php

namespace App\Http\Controllers;

use App\Http\Resources\AsistenciaResource;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;
use Illuminate\Http\JsonResponse;

#[SwaggerSection('Asistencias')]

class ReporteAsistenciaController extends Controller
{
    #[SwaggerSummary('Consulta el historial de asistencias del estudiante autenticado.')]
    #[SwaggerResponse([
        'data' => [
            [
                'id' => 1,
                'fecha' => '2026-10-07',
                'hora_entrada' => '07:10:00',
                'estado' => 'PRESENTE',
                'observaciones' => 'Llegó a las 07:10:00. A tiempo.',
            ],
        ],
    ], 200, 'Historial de asistencia del estudiante')]

    public function historial(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $estudiante = $usuario->estudiante;

        if (!$estudiante) {
            return response()->json([
                'message' => 'El usuario autenticado no tiene un estudiante asociado.'
            ], 404);
        }

        $asistencias = Asistencia::where('estudiante_id', $estudiante->id)
            ->orderByDesc('fecha_asistencia')
            ->orderByDesc('hora_entrada')
            ->get();

        return AsistenciaResource::collection($asistencias)->response();
    }
}