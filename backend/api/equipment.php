<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
AuthGuard::requireApiAuth();

$method = $_SERVER['REQUEST_METHOD'];
$search = (string) ($_GET['search'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$limit  = max(1, min(200, (int) ($_GET['limit'] ?? 50)));
$page   = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

try {
    $repo = new GymRepository();

    if ($method === 'GET') {
        $rows       = $repo->listEquipment($search, $status, $limit, $offset);
        $total      = $repo->countEquipment($search, $status);
        $totalPages = max(1, (int) ceil($total / $limit));

        ApiResponse::ok($rows, [
            'count'      => count($rows),
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalPages,
            'hasPrev'    => $page > 1,
            'hasNext'    => $page < $totalPages,
        ]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            ApiResponse::error('Geçersiz JSON verisi.', 400);
            exit;
        }

        $equipment = $repo->createEquipment($data);
        ApiResponse::ok($equipment, ['message' => 'Equipment created successfully.']);
        exit;
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            ApiResponse::error('Equipment id is required.', 400);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            ApiResponse::error('Geçersiz JSON verisi.', 400);
            exit;
        }

        $equipment = $repo->updateEquipment($id, $data);
        ApiResponse::ok($equipment, ['message' => 'Equipment updated successfully.']);
        exit;
    }

    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            ApiResponse::error('Equipment id is required.', 400);
            exit;
        }

        $repo->deleteEquipment($id);
        ApiResponse::ok(null, ['message' => 'Equipment deleted successfully.']);
        exit;
    }

    ApiResponse::error('Method not allowed.', 405);

} catch (Throwable $e) {
    ApiResponse::error('Server error occurred.', 500, $e->getMessage());
}
