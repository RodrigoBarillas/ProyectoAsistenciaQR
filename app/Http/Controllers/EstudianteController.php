<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEstudianteRequest;
use App\Http\Requests\UpdateEstudianteRequest;
use App\Http\Resources\EstudianteResource;
use App\Models\Estudiante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;

#[SwaggerSection('Estudiantes')]
class EstudianteController extends Controller
{
    #[SwaggerSummary('Lista paginada de estudiantes. Filtra por sección con el query param "seccion_id".')]
    #[SwaggerResponse([
        'data' => [
            [
                'id' => 1,
                'codigo_estudiante' => 'EST-001',
                'nombres' => 'Ana Lucía',
                'apellidos' => 'Pérez López',
                'qr_token' => '550e8400-e29b-41d4-a716-446655440000',
                'seccion_id' => 1,
                'seccion' => [
                    'id' => 1,
                    'nombre' => 'A',
                    'grado_id' => 1,
                    'grado' => [
                        'id' => 1,
                        'nombre' => 'Primero Básico',
                        'estado' => true,
                        'created_at' => '2026-01-10T15:00:00.000000Z',
                        'updated_at' => '2026-01-10T15:00:00.000000Z',
                    ],
                    'estado' => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado' => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
        ],
        'links' => [
            'first' => 'http://localhost:8000/api/estudiantes?page=1',
            'last' => 'http://localhost:8000/api/estudiantes?page=1',
            'prev' => null,
            'next' => null,
        ],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 1,
        ],
    ], 200, 'Listado paginado de estudiantes')]
    public function index(Request $request): JsonResponse
    {
        $estudiantes = Estudiante::with('seccion.grado')
            ->when($request->filled('seccion_id'), fn ($query) => $query->where('seccion_id', $request->query('seccion_id')))
            ->paginate(15);

        return EstudianteResource::collection($estudiantes)->response();
    }

    #[SwaggerSummary('Crea un estudiante y genera su "qr_token" (UUID) automáticamente. Responde 422 si falta un campo requerido, "codigo_estudiante" ya existe, o "seccion_id" no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres' => 'Ana Lucía',
            'apellidos' => 'Pérez López',
            'qr_token' => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id' => 1,
            'seccion' => [
                'id' => 1,
                'nombre' => 'A',
                'grado_id' => 1,
                'grado' => [
                    'id' => 1,
                    'nombre' => 'Primero Básico',
                    'estado' => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado' => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado' => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T15:00:00.000000Z',
        ],
    ], 201, 'Estudiante creado')]
    public function store(StoreEstudianteRequest $request): JsonResponse
    {
        $estudiante = new Estudiante($request->validated());
        $estudiante->qr_token = Str::uuid();
        $estudiante->save();

        return (new EstudianteResource($estudiante->refresh()->load('seccion.grado')))->response()->setStatusCode(201);
    }

    #[SwaggerSummary('Detalle de un estudiante. Responde 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres' => 'Ana Lucía',
            'apellidos' => 'Pérez López',
            'qr_token' => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id' => 1,
            'seccion' => [
                'id' => 1,
                'nombre' => 'A',
                'grado_id' => 1,
                'grado' => [
                    'id' => 1,
                    'nombre' => 'Primero Básico',
                    'estado' => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado' => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado' => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T15:00:00.000000Z',
        ],
    ], 200, 'Detalle del estudiante')]
    public function show(string $estudiante): JsonResponse
    {
        return (new EstudianteResource(Estudiante::with('seccion.grado')->findOrFail($estudiante)))->response();
    }

    #[SwaggerSummary('Edita un estudiante. El "qr_token" nunca se modifica, aunque se envíe en el body. Responde 422 en validación y 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres' => 'Ana Lucía',
            'apellidos' => 'Pérez Gómez',
            'qr_token' => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id' => 1,
            'seccion' => [
                'id' => 1,
                'nombre' => 'A',
                'grado_id' => 1,
                'grado' => [
                    'id' => 1,
                    'nombre' => 'Primero Básico',
                    'estado' => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado' => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado' => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T16:00:00.000000Z',
        ],
    ], 200, 'Estudiante actualizado')]
    public function update(UpdateEstudianteRequest $request, string $estudiante): JsonResponse
    {
        $modelo = Estudiante::findOrFail($estudiante);
        $modelo->update($request->validated());

        return (new EstudianteResource($modelo->load('seccion.grado')))->response();
    }

    #[SwaggerSummary('Inactiva un estudiante (borrado lógico: estado=false). Responde 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres' => 'Ana Lucía',
            'apellidos' => 'Pérez López',
            'qr_token' => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id' => 1,
            'seccion' => [
                'id' => 1,
                'nombre' => 'A',
                'grado_id' => 1,
                'grado' => [
                    'id' => 1,
                    'nombre' => 'Primero Básico',
                    'estado' => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado' => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado' => false,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T16:30:00.000000Z',
        ],
    ], 200, 'Estudiante inactivado')]
    public function destroy(string $estudiante): JsonResponse
    {
        $modelo = Estudiante::findOrFail($estudiante);
        $modelo->update(['estado' => false]);

        return (new EstudianteResource($modelo->load('seccion.grado')))->response();
    }
}
