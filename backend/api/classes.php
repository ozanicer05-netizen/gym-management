<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$search = (string) ($_GET['search'] ?? '');
$level = (string) ($_GET['level'] ?? '');
$limit = (int) ($_GET['limit'] ?? 50);

try {
    $repo = new GymRepository();
    $rows = $repo->listClasses($search, $level, $limit);
    ApiResponse::ok($rows, ['count' => count($rows)]);
} catch (Throwable $e) {
    ApiResponse::error('Sunucu hatası oluştu.', 500, $e->getMessage());
}
