<?php

namespace App\Utils;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponse
{
    /**
     * 200 – Successful operation with data payload.
     *
     * @param JsonResource|ResourceCollection|array<string, mixed>|null $data
     */
    public static function success(
        JsonResource|ResourceCollection|array|null $data = null,
        string $message = 'Operation successful.',
        int $status = 200,
    ): JsonResponse {
        $payload = $data instanceof JsonResource || $data instanceof ResourceCollection
            ? $data->resolve(request())
            : $data;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $payload,
        ], $status);
    }

    /**
     * 201 – Resource created successfully.
     *
     * @param JsonResource|array<string, mixed>|null $data
     */
    public static function created(
        JsonResource|array|null $data = null,
        string $message = 'Resource created successfully.',
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    /**
     * Generic error response.
     *
     * @param array<string, mixed>|null $errors
     */
    public static function error(
        string $message = 'An unexpected error occurred.',
        int $status = 500,
        array|null $errors = null,
    ): JsonResponse {
        $body = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }

    /**
     * 401 – Unauthenticated.
     */
    public static function unauthorized(
        string $message = 'Unauthorized.',
    ): JsonResponse {
        return self::error($message, 401);
    }

    /**
     * 403 – Forbidden.
     */
    public static function forbidden(
        string $message = 'Forbidden.',
    ): JsonResponse {
        return self::error($message, 403);
    }

    /**
     * 404 – Resource not found.
     */
    public static function notFound(
        string $message = 'Resource not found.',
    ): JsonResponse {
        return self::error($message, 404);
    }

    /**
     * 422 – Validation failed.
     *
     * @param array<string, mixed> $errors
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed.',
    ): JsonResponse {
        return self::error($message, 422, $errors);
    }
}
