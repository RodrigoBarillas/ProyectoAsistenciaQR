<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\StoreEstudianteRequest;
use App\Http\Requests\UpdateEstudianteRequest;
use App\Http\Resources\EstudianteResource;
use App\Models\Estudiante;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;

#[SwaggerSection('Estudiantes')]
class EstudianteController extends Controller
{
    #[SwaggerSummary('Lista paginada de estudiantes. Filtra por "seccion_id", "grado_id" y "estado" (true/false), busca por nombres/apellidos/codigo_estudiante con "search", y admite "per_page" (máx. 100) además de "page".')]
    #[SwaggerResponse([
        'data' => [
            [
                'id'                => 1,
                'codigo_estudiante' => 'EST-001',
                'nombres'           => 'Ana Lucía',
                'apellidos'         => 'Pérez López',
                'qr_token'          => '550e8400-e29b-41d4-a716-446655440000',
                'seccion_id'        => 1,
                'seccion'           => [
                    'id'       => 1,
                    'nombre'   => 'A',
                    'grado_id' => 1,
                    'grado'    => [
                        'id'         => 1,
                        'nombre'     => 'Primero Básico',
                        'estado'     => true,
                        'created_at' => '2026-01-10T15:00:00.000000Z',
                        'updated_at' => '2026-01-10T15:00:00.000000Z',
                    ],
                    'estado'     => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado'     => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
        ],
        'links' => [
            'first' => 'http://localhost:8000/api/estudiantes?page=1',
            'last'  => 'http://localhost:8000/api/estudiantes?page=1',
            'prev'  => null,
            'next'  => null,
        ],
        'meta' => [
            'current_page' => 1,
            'last_page'    => 1,
            'per_page'     => 15,
            'total'        => 1,
        ],
    ], 200, 'Listado paginado de estudiantes')]
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $estudiantes = Estudiante::with('seccion.grado')
            ->when($request->filled('seccion_id'), fn ($query) => $query->where('seccion_id', $request->query('seccion_id')))
            ->when($request->filled('grado_id'), fn ($query) => $query->whereHas(
                'seccion',
                fn ($seccionQuery) => $seccionQuery->where('grado_id', $request->query('grado_id')),
            ))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->boolean('estado')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->query('search');

                $query->where(function ($query) use ($search) {
                    $query->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('codigo_estudiante', 'like', "%{$search}%");
                });
            })
            ->paginate($perPage);

        return EstudianteResource::collection($estudiantes)->response();
    }

    #[SwaggerSummary('Crea un estudiante junto con su usuario del sistema (rol Alumno). Genera una contraseña temporal y la devuelve una única vez. El "qr_token" (UUID) se genera automáticamente.')]
    #[SwaggerResponse([
        'data' => [
            'id'                => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres'           => 'Ana Lucía',
            'apellidos'         => 'Pérez López',
            'qr_token'          => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id'        => 1,
            'seccion'           => [
                'id'       => 1,
                'nombre'   => 'A',
                'grado_id' => 1,
                'grado'    => [
                    'id'         => 1,
                    'nombre'     => 'Primero Básico',
                    'estado'     => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado'     => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado'     => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T15:00:00.000000Z',
        ],
        'temporal_password' => 'xK9mP2qR4zAb',
    ], 201, 'Estudiante y usuario creados')]
    public function store(StoreEstudianteRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Generate a secure temporary password (12 chars, letters + numbers, no symbols
        // so it is easy to communicate verbally or on paper for first login).
        $temporalPassword = Str::password(12, letters: true, numbers: true, symbols: false);

        $estudiante = DB::transaction(function () use ($validated, $temporalPassword) {
            // Resolve the Alumno role — fails loudly if the RoleSeeder has not run.
            $role = Role::where('nombre', RoleEnum::ALUMNO->value)->firstOrFail();

            // Create the system user for this student.
            // name = full name so the JWT / profile makes sense out of the box.
            $user = User::create([
                'name'     => trim("{$validated['nombres']} {$validated['apellidos']}"),
                'email'    => $validated['email'],
                'password' => $temporalPassword,
                'role_id'  => $role->id,
                'status'   => true,
            ]);

            // Create the student record linked to the new user.
            $estudiante           = new Estudiante($validated);
            $estudiante->qr_token = Str::uuid()->toString();
            $estudiante->user_id  = $user->id;
            $estudiante->save();



            return $estudiante;
        });

        return response()->json([
            'data'              => new EstudianteResource($estudiante->refresh()->load('seccion.grado')),
            'temporal_password' => $temporalPassword,
        ], 201);
    }

    #[SwaggerSummary('Detalle de un estudiante. Responde 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id'                => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres'           => 'Ana Lucía',
            'apellidos'         => 'Pérez López',
            'qr_token'          => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id'        => 1,
            'seccion'           => [
                'id'       => 1,
                'nombre'   => 'A',
                'grado_id' => 1,
                'grado'    => [
                    'id'         => 1,
                    'nombre'     => 'Primero Básico',
                    'estado'     => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado'     => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado'     => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T15:00:00.000000Z',
        ],
    ], 200, 'Detalle del estudiante')]
    public function show(string $estudiante): JsonResponse
    {
        return (new EstudianteResource(Estudiante::with('seccion.grado')->findOrFail($estudiante)))->response();
    }

    #[SwaggerSummary('Edita un estudiante. El "qr_token" nunca se modifica, aunque se envíe en el body. Responde 422 en validación (incluye sección inexistente o inactiva) y 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id'                => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres'           => 'Ana Lucía',
            'apellidos'         => 'Pérez Gómez',
            'qr_token'          => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id'        => 1,
            'seccion'           => [
                'id'       => 1,
                'nombre'   => 'A',
                'grado_id' => 1,
                'grado'    => [
                    'id'         => 1,
                    'nombre'     => 'Primero Básico',
                    'estado'     => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado'     => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado'     => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T16:00:00.000000Z',
        ],
    ], 200, 'Estudiante actualizado')]
    public function update(UpdateEstudianteRequest $request, string $estudiante): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($estudiante);
        $estudiante->update($request->validated());

        return (new EstudianteResource($estudiante->load('seccion.grado')))->response();
    }

    #[SwaggerSummary('Inactiva un estudiante (borrado lógico: estado=false). Responde 404 si el id no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id'                => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres'           => 'Ana Lucía',
            'apellidos'         => 'Pérez López',
            'qr_token'          => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id'        => 1,
            'seccion'           => [
                'id'       => 1,
                'nombre'   => 'A',
                'grado_id' => 1,
                'grado'    => [
                    'id'         => 1,
                    'nombre'     => 'Primero Básico',
                    'estado'     => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado'     => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado'     => false,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T16:30:00.000000Z',
        ],
    ], 200, 'Estudiante inactivado')]
    public function destroy(string $estudiante): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($estudiante);
        $estudiante->update(['estado' => false]);

        return (new EstudianteResource($estudiante->load('seccion.grado')))->response();
    }

    #[SwaggerSummary('Busca un estudiante por su "qr_token" (UUID). Lo usará el módulo de asistencia. Responde 404 si el token no existe.')]
    #[SwaggerResponse([
        'data' => [
            'id'                => 1,
            'codigo_estudiante' => 'EST-001',
            'nombres'           => 'Ana Lucía',
            'apellidos'         => 'Pérez López',
            'qr_token'          => '550e8400-e29b-41d4-a716-446655440000',
            'seccion_id'        => 1,
            'seccion'           => [
                'id'       => 1,
                'nombre'   => 'A',
                'grado_id' => 1,
                'grado'    => [
                    'id'         => 1,
                    'nombre'     => 'Primero Básico',
                    'estado'     => true,
                    'created_at' => '2026-01-10T15:00:00.000000Z',
                    'updated_at' => '2026-01-10T15:00:00.000000Z',
                ],
                'estado'     => true,
                'created_at' => '2026-01-10T15:00:00.000000Z',
                'updated_at' => '2026-01-10T15:00:00.000000Z',
            ],
            'estado'     => true,
            'created_at' => '2026-01-10T15:00:00.000000Z',
            'updated_at' => '2026-01-10T15:00:00.000000Z',
        ],
    ], 200, 'Detalle del estudiante encontrado por qr_token')]
    public function showByQrToken(string $qr_token): JsonResponse
    {
        $estudiante = Estudiante::with('seccion.grado')->where('qr_token', $qr_token)->firstOrFail();

        return (new EstudianteResource($estudiante))->response();
    }
}
