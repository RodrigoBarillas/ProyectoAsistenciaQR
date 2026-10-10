<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Mock\AuthMock;
use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\Authentication\RefreshRequest;
use App\Http\Resources\Authentication\MeResource;
use App\Services\Authentication\Contracts\AuthServiceInterface;
use App\Utils\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;

#[SwaggerSection('Authentication')]
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}



    #[SwaggerResponse(AuthMock::LOGIN_SUCCESS)]
    #[SwaggerSummary('Inicia sesión y obtiene un token de acceso y un token de actualización.')]
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $resource = $this->authService->login($request);

            return ApiResponse::success($resource, 'Login successful.');
        } catch (AuthenticationException $e) {
            return ApiResponse::unauthorized($e->getMessage());
        }
    }


    #[SwaggerResponse(AuthMock::REFRESH_SUCCESS)]
    #[SwaggerSummary('Refresca el token de acceso utilizando un token de actualización válido.')]
    public function refresh(RefreshRequest $request): JsonResponse
    {
        try {
            $resource = $this->authService->refresh($request);

            return ApiResponse::success($resource, 'Token refreshed successfully.');
        } catch (AuthenticationException $e) {
            return ApiResponse::unauthorized($e->getMessage());
        }
    }


    #[SwaggerResponse(AuthMock::LOGOUT_SUCCESS)]
    #[SwaggerSummary('Cierra sesión y revoca el token de acceso actual.')]
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return ApiResponse::success(message: 'Successfully logged out.');
    }

    #[SwaggerResponse(AuthMock::ME_SUCCESS_ALUMNO)]
    #[SwaggerSummary('Perfil del usuario autenticado: id, nombre, email y rol. Si el usuario es Alumno, incluye además su "estudiante" (código, nombres, sección y grado); para Docente/Admin "estudiante" es null.')]
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'estudiante.seccion.grado']);

        return ApiResponse::success(new MeResource($user), 'Perfil obtenido correctamente.');
    }
}
