<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradoRequest;
use App\Http\Requests\UpdateGradoRequest;
use App\Http\Resources\GradoResource;
use App\Models\Grado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;

#[SwaggerSection('Grados')]
class GradoController extends Controller
{
    #[SwaggerSummary('Lista paginada de grados. Filtra por estado con el query param "estado" (true/false).')]
    #[SwaggerResponse([
        'data' => [
            [
                'id' => 1,
                'nombre' => 'Primero Básico',
                'estado' => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
        ],
        'links' => [
            'first' => 'http://localhost:8000/api/grados?page=1',
            'last' => 'http://localhost:8000/api/grados?page=1',
            'prev' => null,
            'next' => null,
        ],
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => 1,
        ],
    ], 200, 'Listado paginado de grados')]
    public function index(Request $request): JsonResponse
    {
        $grados = Grado::query()
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->boolean('estado')))
            ->paginate(15);

        return GradoResource::collection($grados)->response();
    }

    #[SwaggerSummary('Crea un grado. Responde 422 si "nombre" falta, excede 50 caracteres o ya existe.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'nombre' => 'Primero Básico',
            'estado' => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T15:00:00.000000Z',
        ],
    ], 201, 'Grado creado')]
    public function store(StoreGradoRequest $request): JsonResponse
    {
        $grado = Grado::create($request->validated())->refresh();

        return (new GradoResource($grado))->response()->setStatusCode(201);
    }

    #[SwaggerSummary('Detalle de un grado. Responde 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'nombre' => 'Primero Básico',
            'estado' => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T15:00:00.000000Z',
        ],
    ], 200, 'Detalle del grado')]
    public function show(string $grado): JsonResponse
    {
        return (new GradoResource(Grado::findOrFail($grado)))->response();
    }

    #[SwaggerSummary('Edita un grado. Responde 422 en validación y 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'nombre' => 'Primero Básico',
            'estado' => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T16:00:00.000000Z',
        ],
    ], 200, 'Grado actualizado')]
    public function update(UpdateGradoRequest $request, string $grado): JsonResponse
    {
        $grado = Grado::findOrFail($grado);
        $grado->update($request->validated());

        return (new GradoResource($grado))->response();
    }

    #[SwaggerSummary('Inactiva un grado (borrado lógico: estado=false). Responde 404 si el id no existe y 422 si tiene secciones activas asociadas.')]
    #[SwaggerResponse([
        'data' => [
            'id' => 1,
            'nombre' => 'Primero Básico',
            'estado' => false,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T16:30:00.000000Z',
        ],
    ], 200, 'Grado inactivado')]
    public function destroy(string $grado): JsonResponse
    {
        $grado = Grado::findOrFail($grado);

        if ($grado->secciones()->where('estado', true)->exists()) {
            throw ValidationException::withMessages([
                'estado' => ['No se puede inactivar el grado porque tiene secciones activas asociadas.'],
            ]);
        }

        $grado->update(['estado' => false]);

        return (new GradoResource($grado))->response();
    }
}
