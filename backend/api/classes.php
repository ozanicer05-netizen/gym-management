<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
AuthGuard::requireApiAuth();

$method = $_SERVER['REQUEST_METHOD'];
$search = (string) ($_GET['search'] ?? '');
$level  = (string) ($_GET['level'] ?? '');
$limit  = max(1, min(200, (int) ($_GET['limit'] ?? 50)));
$page   = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

try {
    $repo = new GymRepository();

    if ($method === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $class = $repo->getClassById($id);
            if (!$class) {
                ApiResponse::error('Class not found.', 404);
                exit;
            }
            ApiResponse::ok($class);
            exit;
        }

        $rows       = $repo->listClasses($search, $level, $limit, $offset);
        $total      = $repo->countClasses($search, $level);
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
    ApiResponse::error('Server error occurred.', 500, $e->getMessage());
}
