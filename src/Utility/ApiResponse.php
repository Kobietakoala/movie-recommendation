<?php

declare(strict_types=1);

namespace App\Utility;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(string $message = 'Error', int $statusCode = 400): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}