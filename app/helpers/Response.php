<?php

namespace App\Helpers;

class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(string $message = 'Error', mixed $data = null, int $statusCode = 400): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function paginated(array $items, int $total, int $page, int $perPage, string $message = 'Success'): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
                'total_pages' => ceil($total / $perPage),
            ],
        ]);
    }

    public static function notFound(string $message = 'Not Found'): void
    {
        self::error($message, null, 404);
    }

    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, null, 401);
    }

    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, null, 403);
    }

    public static function unprocessable(array $errors): void
    {
        self::json([
            'success' => false,
            'message' => 'Validation Error',
            'errors' => $errors,
        ], 422);
    }
}
