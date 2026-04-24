<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
AuthGuard::requireApiAuth();

$type = (string) ($_GET['type'] ?? '');

try {
    $conn = Database::connect();

    switch ($type) {
        case 'users':
            $sql = "SELECT user_id, name, surname, email FROM users WHERE status = 'active' ORDER BY name, surname LIMIT 1000";
            break;
        case 'users_without_member':
            $sql = "SELECT u.user_id, u.name, u.surname, u.email
                    FROM users u
                    LEFT JOIN members m ON m.user_id = u.user_id
                    WHERE m.member_id IS NULL AND u.status = 'active'
                    ORDER BY u.name, u.surname LIMIT 1000";
            break;
        case 'users_without_trainer':
            $sql = "SELECT u.user_id, u.name, u.surname, u.email
                    FROM users u
                    LEFT JOIN trainers t ON t.user_id = u.user_id
                    WHERE t.trainer_id IS NULL AND u.status = 'active'
                    ORDER BY u.name, u.surname LIMIT 1000";
            break;
        case 'branches':
            $sql = "SELECT branch_id, branch_name FROM branches WHERE status = 'active' ORDER BY branch_name LIMIT 1000";
            break;
        case 'trainers':
            $sql = "SELECT t.trainer_id, CONCAT(u.name, ' ', u.surname) AS trainer_name
                    FROM trainers t JOIN users u ON t.user_id = u.user_id
                    WHERE t.availability_status = 'active'
                    ORDER BY u.name, u.surname LIMIT 1000";
            break;
        case 'members':
            $sql = "SELECT m.member_id, CONCAT(u.name, ' ', u.surname) AS member_name
                    FROM members m JOIN users u ON m.user_id = u.user_id
                    WHERE m.status = 'active'
                    ORDER BY u.name, u.surname LIMIT 1000";
            break;
        case 'packages':
            $sql = "SELECT package_id, package_name, duration_days, price FROM packages WHERE is_active = 1 ORDER BY package_name LIMIT 1000";
            break;
        case 'equipment_categories':
            $sql = "SELECT category_id, category_name FROM equipment_categories ORDER BY category_name LIMIT 1000";
            break;
        default:
            ApiResponse::error('Unknown lookup type.', 400);
            exit;
    }

    $result = $conn->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    ApiResponse::ok($rows);
} catch (Throwable $e) {
    ApiResponse::error('Server error occurred.', 500, $e->getMessage());
}
