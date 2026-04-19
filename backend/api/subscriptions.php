<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$search = (string) ($_GET['search'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$limit = (int) ($_GET['limit'] ?? 50);

try {
    $repo = new GymRepository();
    $rows = $repo->listSubscriptions($search, $status, $limit);
    ApiResponse::ok($rows, ['count' => count($rows)]);
} catch (Throwable $e) {
    ApiResponse::error('Sunucu hatası oluştu.', 500, $e->getMessage());
}
