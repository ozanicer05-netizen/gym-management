<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['auth_user'])) {
    ApiResponse::error('Unauthorized', 401);
    exit;
}

ApiResponse::ok($_SESSION['auth_user']);
