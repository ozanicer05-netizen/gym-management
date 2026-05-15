<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
AuthGuard::requireApiAuth();

$search = (string) ($_GET['search'] ?? '');
$status = (string) ($_GET['status'] ?? '');

try {
    $repo = new GymRepository();
    $total = $repo->countMembers($search, $status);
    $rows = $repo->listMembers($search, $status, max(1, $total), 0);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="members_export.csv"');

    $columns = ['member_id', 'name', 'surname', 'email', 'phone', 'branch_name', 'join_date', 'status'];

    ob_end_clean();

    echo "\xEF\xBB\xBF";
    echo implode(';', $columns) . "\r\n";

    foreach ($rows as $row) {
        $line = [];
        foreach ($columns as $col) {
            $val = str_replace('"', '""', (string) ($row[$col] ?? ''));
            $line[] = '"' . $val . '"';
        }
        echo implode(';', $line) . "\r\n";
    }

    exit;
} catch (Throwable $e) {
    ApiResponse::error('Failed to export members.', 500, $e->getMessage());
}
