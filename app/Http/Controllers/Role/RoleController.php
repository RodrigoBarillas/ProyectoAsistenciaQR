<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Mock\RoleMock;
use App\Http\Requests\Role\RoleRequest;
use App\Models\Role;
use App\Services\Role\Contracts\RoleServiceInterface;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;

#[SwaggerSection('Roles')]
class RoleController extends Controller
{
    public function __construct(
        private readonly RoleServiceInterface $roleService,
    ) {}


    #[SwaggerResponse(RoleMock::ROLES_INDEX)]
    #[SwaggerSummary('Retorna la lista de todos los roles disponibles con sus permisos asociados.')]
    public function index(): JsonResponse
    {
        $collection = $this->roleService->index();

        return ApiResponse::success($collection, 'Roles retrieved successfully.');
    }


    #[SwaggerResponse(RoleMock::ROLE_SHOW)]
    #[SwaggerSummary('Retorna el detalle de un rol identificado por su ID.')]
    public function show(mixed $role): JsonResponse
    {
        $role = $this->roleService->findRoleById($role);

        $resource = $this->roleService->show($role);

        return ApiResponse::success($resource, 'Role retrieved successfully.');
    }


    #[SwaggerResponse(RoleMock::ROLE_STORE)]
    #[SwaggerSummary('Crea un nuevo rol y opcionalmente le asigna permisos.')]
    public function store(RoleRequest $request): JsonResponse
    {
        $resource = $this->roleService->store($request);

        return ApiResponse::created($resource, 'Role created successfully.');
    }


    #[SwaggerResponse(RoleMock::ROLE_UPDATE)]
    #[SwaggerSummary('Actualiza un rol existente identificado por su ID.')]
    public function update(RoleRequest $request, mixed $role): JsonResponse
    {
        $role = $this->roleService->findRoleById($role);

        $resource = $this->roleService->update($request, $role);

        return ApiResponse::success($resource, 'Role updated successfully.');
    }


    #[SwaggerResponse(RoleMock::ROLE_DESTROY)]
    #[SwaggerSummary('Elimina un rol identificado por su ID.')]
    public function destroy(mixed $role): JsonResponse
    {
        $role = $this->roleService->findRoleById($role);

        $this->roleService->destroy($role);

        return ApiResponse::success(message: 'Role deleted successfully.');
    }
}
