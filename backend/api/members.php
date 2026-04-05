<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../src/GymRepository.php';

$search = isset($_GET['search']) ? (string) $_GET['search'] : '';
$status = isset($_GET['status']) ? (string) $_GET['status'] : '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;

try {
    $repo = new GymRepository();
    $members = $repo->listMembers($search, $status, $limit);

    echo json_encode([
        'ok' => true,
        'meta' => [
            'count' => count($members),
        ],
        'data' => $members,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'error' => 'Sunucu hatası oluştu.',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
