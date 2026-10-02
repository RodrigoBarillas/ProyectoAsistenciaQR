<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Swagger\Attributes\SwaggerSection;

#[SwaggerSection('Example')]
class ExampleController extends Controller
{

    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Hello World'
        ]);
    }

    public function store(): JsonResponse
    {
        return response()->json([
            'message' => 'Resource created'
        ]);
    }

    public function show(mixed $id): JsonResponse
    {
        return response()->json([
            'message' => "Showing resource with ID: $id"
        ]);
    }

    public function update(mixed $id): JsonResponse
    {
        return response()->json([
            'message' => "Updating resource with ID: $id"
        ]);
    }

    public function destroy(mixed $id): JsonResponse
    {
        return response()->json([
            'message' => "Deleting resource with ID: $id"
        ]);
    }

}
