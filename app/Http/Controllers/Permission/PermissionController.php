<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Http\Mock\PermissionMock;
use App\Services\Permission\Contracts\PermissionServiceInterface;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Laravel\Swagger\Attributes\SwaggerResponse;
use Laravel\Swagger\Attributes\SwaggerSection;
use Laravel\Swagger\Attributes\SwaggerSummary;

#[SwaggerSection('Permissions')]
class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionServiceInterface $permissionService,
    ) {}


    #[SwaggerResponse(PermissionMock::PERMISSIONS_INDEX)]
    #[SwaggerSummary('Retorna la lista de todos los permisos disponibles en el sistema.')]
    public function index(): JsonResponse
    {
        $collection = $this->permissionService->index();

        return ApiResponse::success($collection, 'Permissions retrieved successfully.');
    }
}
