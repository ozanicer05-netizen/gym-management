<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

try {
    $repo = new GymRepository();
    ApiResponse::ok($repo->getDashboardStats());
} catch (Throwable $e) {
    ApiResponse::error('Sunucu hatası oluştu.', 500, $e->getMessage());
}
