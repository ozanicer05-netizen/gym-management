<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$email = trim((string) ($payload['email'] ?? ''));
$password = (string) ($payload['password'] ?? '');

if ($email === '' || $password === '') {
    ApiResponse::error('Email and password are required.', 422);
    exit;
}

try {
    $conn = Database::connect();
    $safeEmail = $conn->real_escape_string($email);

    $sql = "
        SELECT u.user_id, u.name, u.surname, u.email, u.password_hash, u.branch_id, r.role_name
        FROM users u
        LEFT JOIN user_roles ur ON ur.user_id = u.user_id
        LEFT JOIN roles r ON r.role_id = ur.role_id
        WHERE u.email = '{$safeEmail}'
        LIMIT 1
    ";

    $result = $conn->query($sql);
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        ApiResponse::error('Invalid credentials.', 401);
        exit;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['auth_user'] = [
        'id'        => (int) $user['user_id'],
        'name'      => trim(((string) $user['name']) . ' ' . ((string) $user['surname'])),
        'email'     => (string) $user['email'],
        'role'      => (string) ($user['role_name'] ?? 'member'),
        'branch_id' => $user['branch_id'] !== null ? (int) $user['branch_id'] : null,
    ];

    ApiResponse::ok($_SESSION['auth_user']);
} catch (Throwable $e) {
    ApiResponse::error('Server error occurred.', 500, $e->getMessage());
}
