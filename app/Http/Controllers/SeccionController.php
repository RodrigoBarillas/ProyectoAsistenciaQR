<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSeccionRequest;
use App\Http\Requests\UpdateSeccionRequest;
use App\Http\Resources\SeccionResource;
use App\Models\Seccion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;

#[SwaggerSection('Secciones')]
class SeccionController extends Controller
{
    #[SwaggerSummary('Lista paginada de secciones. Filtra por grado con el query param "grado_id".')]
    #[SwaggerResponse([
        'data' => [
            [
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
        ],
        'links' => [
            'first' => 'http://localhost:8000/api/secciones?page=1',
            'last' => 'http://localhost:8000/api/secciones?page=1',
            'prev' => null,
            'next' => null,
        ],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 1,
        ],
    ], 200, 'Listado paginado de secciones')]
    public function index(Request $request): JsonResponse
    {
        $secciones = Seccion::with('grado')
            ->when($request->filled('grado_id'), fn ($query) => $query->where('grado_id', $request->query('grado_id')))
            ->paginate(15);

        return SeccionResource::collection($secciones)->response();
    }

    #[SwaggerSummary('Crea una sección dentro de un grado. Responde 422 si "nombre" falta, excede 20 caracteres, ya existe en ese grado, o "grado_id" no existe.')]
    #[SwaggerResponse([
        'data' => [
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
    ], 201, 'Sección creada')]
    public function store(StoreSeccionRequest $request): JsonResponse
    {
        $seccion = Seccion::create($request->validated())->refresh()->load('grado');

        return (new SeccionResource($seccion))->response()->setStatusCode(201);
    }

    #[SwaggerSummary('Detalle de una sección. Responde 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
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
    ], 200, 'Detalle de la sección')]
    public function show(string $seccione): JsonResponse
    {
        return (new SeccionResource(Seccion::with('grado')->findOrFail($seccione)))->response();
    }

    #[SwaggerSummary('Edita una sección. Responde 422 en validación y 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'nombre' => 'B',
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
            'updated_at' => '2026-01-10T16:00:00.000000Z',
        ],
    ], 200, 'Sección actualizada')]
    public function update(UpdateSeccionRequest $request, string $seccione): JsonResponse
    {
        $seccion = Seccion::findOrFail($seccione);
        $seccion->update($request->validated());

        return (new SeccionResource($seccion->load('grado')))->response();
    }

    #[SwaggerSummary('Inactiva una sección (borrado lógico: estado=false). Responde 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
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
            'estado' => false,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T16:30:00.000000Z',
        ],
    ], 200, 'Sección inactivada')]
    public function destroy(string $seccione): JsonResponse
    {
        $seccion = Seccion::findOrFail($seccione);
        $seccion->update(['estado' => false]);

        return (new SeccionResource($seccion->load('grado')))->response();
    }
}
