<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseService
{
    public static function success(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    public static function error(string $message, int $status = 400): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }
}
