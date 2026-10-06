<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrarAsistenciaRequest;
use App\Models\Asistencia;
use App\Models\Estudiante;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;

#[SwaggerSection('Asistencias')]
class AsistenciaController extends Controller
{
    #[SwaggerSummary('Registra la asistencia de un estudiante escaneando su QR. "qr_token" (UUID)')]
    #[SwaggerResponse([
        'success' => true,
        'ingreso_permitido' => true,
        'message' => 'Asistencia registrada correctamente.',
        'data' => [
            'id' => 1,
            'estudiante_id' => 1,
            'nombre' => 'Luis Pérez',
            'fecha_asistencia' => '2026-10-05',
            'hora_entrada' => '07:05:12',
            'estado' => 'presente',
        ],
    ], 201, 'Asistencia registrada, ingreso permitido')]
    public function registrar(RegistrarAsistenciaRequest $request): JsonResponse
    {
        $estudiante = Estudiante::where('qr_token', $request->validated('qr_token'))->firstOrFail();

        $ahora = Carbon::now(config('asistencia.zona_horaria'));
        $fecha = $ahora->toDateString();
        $hora  = $ahora->format('H:i:s');

        $yaRegistrada = Asistencia::where('estudiante_id', $estudiante->id)
            ->where('fecha_asistencia', $fecha)
            ->exists();

        if ($yaRegistrada) {
            return $this->respuestaDuplicado();
        }

        $inicio = $ahora->copy()->setTimeFromTimeString(config('asistencia.hora_inicio'));
        $limite = $inicio->copy()->addMinutes(config('asistencia.tolerancia_minutos'));

        $esAusente = $ahora->greaterThanOrEqualTo($limite);
        $estado    = $esAusente ? 'ausente' : 'presente';
        $limiteTxt = $limite->format('H:i');

        $observaciones = $esAusente
            ? "Llegó a las {$hora}. Ingreso no permitido (límite {$limiteTxt})."
            : "Llegó a las {$hora}. Ingreso permitido.";

        try {
            $asistencia = Asistencia::create([
                'estudiante_id'    => $estudiante->id,
                'fecha_asistencia' => $fecha,
                'hora_entrada'     => $hora,
                'estado'           => $estado,
                'observaciones'    => $observaciones,
                'registrado_por'   => $request->user()->id,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->respuestaDuplicado();
            }
            throw $e;
        }

        $data = [
            'id'               => $asistencia->id,
            'estudiante_id'    => $estudiante->id,
            "nombre"           => $estudiante->nombres . ' ' . $estudiante->apellidos,
            'fecha_asistencia' => $fecha,
            'hora_entrada'     => $hora,
            'estado'           => $estado,
        ];

        if ($esAusente) {
            return response()->json([
                'success'           => false,
                'ingreso_permitido' => false,
                'message'           => "Llegada a las {$limiteTxt} o después. Se registró como ausencia y no se permite el ingreso a clases.",
                'data'              => $data,
            ], 403);
        }

        return response()->json([
            'success'           => true,
            'ingreso_permitido' => true,
            'message'           => 'Asistencia registrada correctamente.',
            'data'              => $data,
        ], 201);
    }

    private function respuestaDuplicado(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'La asistencia de este estudiante ya fue registrada el día de hoy.',
        ], 409);
    }
}