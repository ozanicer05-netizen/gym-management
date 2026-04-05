<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../src/GymRepository.php';

try {
    $repo = new GymRepository();

    echo json_encode([
        'ok' => true,
        'data' => $repo->getDashboardStats(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'error' => 'Sunucu hatası oluştu.',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
