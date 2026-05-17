<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
AuthGuard::requireApiAuth();

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$authUser      = $_SESSION['auth_user'] ?? [];
$sessionBranch = isset($authUser['branch_id']) && $authUser['branch_id'] !== null
                 ? (int) $authUser['branch_id'] : null;

try {
    $repo = new GymRepository();
    ApiResponse::ok($repo->getDashboardStats($sessionBranch));
} catch (Throwable $e) {
    ApiResponse::error('Server error occurred.', 500, $e->getMessage());
}
