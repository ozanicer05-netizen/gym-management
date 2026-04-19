<?php

declare(strict_types=1);

final class ApiResponse
{
    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    public static function ok(mixed $data, array $meta = []): void
    {
        self::json([
            'ok' => true,
            'meta' => $meta,
            'data' => $data,
        ]);
    }

    public static function error(string $message, int $status = 500, ?string $detail = null): void
    {
        self::json([
            'ok' => false,
            'error' => $message,
            'detail' => $detail,
        ], $status);
    }
}
