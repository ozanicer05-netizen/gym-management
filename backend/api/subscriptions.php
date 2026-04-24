<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $repo = new GymRepository();

    if ($method === 'GET') {
        $search = (string) ($_GET['search'] ?? '');
        $status = (string) ($_GET['status'] ?? '');
        $limit = (int) ($_GET['limit'] ?? 50);

        $rows = $repo->listSubscriptions($search, $status, $limit);
        ApiResponse::ok($rows, ['count' => count($rows)]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            ApiResponse::error('Geçersiz JSON verisi.', 400);
            exit;
        }

        $subscription = $repo->createSubscription($data);
        ApiResponse::ok($subscription, ['message' => 'Subscription created successfully.']);
        exit;
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            ApiResponse::error('Subscription id is required.', 400);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            ApiResponse::error('Geçersiz JSON verisi.', 400);
            exit;
        }

        $subscription = $repo->updateSubscription($id, $data);
        ApiResponse::ok($subscription, ['message' => 'Subscription updated successfully.']);
        exit;
    }

    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            ApiResponse::error('Subscription id is required.', 400);
            exit;
        }

        $repo->deleteSubscription($id);
        ApiResponse::ok(null, ['message' => 'Subscription deleted successfully.']);
        exit;
    }

    ApiResponse::error('Method not allowed.', 405);

} catch (Throwable $e) {
    ApiResponse::error('Sunucu hatası oluştu.', 500, $e->getMessage());
}
