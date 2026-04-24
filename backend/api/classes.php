<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $repo = new GymRepository();

    if ($method === 'GET') {
        $search = (string) ($_GET['search'] ?? '');
        $level = (string) ($_GET['level'] ?? '');
        $limit = (int) ($_GET['limit'] ?? 50);

        $rows = $repo->listClasses($search, $level, $limit);
        ApiResponse::ok($rows, ['count' => count($rows)]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            ApiResponse::error('Geçersiz JSON verisi.', 400);
            exit;
        }

        $class = $repo->createClass($data);
        ApiResponse::ok($class, ['message' => 'Class created successfully.']);
        exit;
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            ApiResponse::error('Class id is required.', 400);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            ApiResponse::error('Geçersiz JSON verisi.', 400);
            exit;
        }

        $class = $repo->updateClass($id, $data);
        ApiResponse::ok($class, ['message' => 'Class updated successfully.']);
        exit;
    }

    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            ApiResponse::error('Class id is required.', 400);
            exit;
        }

        $repo->deleteClass($id);
        ApiResponse::ok(null, ['message' => 'Class deleted successfully.']);
        exit;
    }

    ApiResponse::error('Method not allowed.', 405);

} catch (Throwable $e) {
    ApiResponse::error('Sunucu hatası oluştu.', 500, $e->getMessage());
}
