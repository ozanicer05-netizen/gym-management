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

    @ini_set('display_errors', '0');
    error_reporting(0);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="members_export.csv"');

    $out = fopen('php://output', 'w');
    if ($out === false) {
        throw new RuntimeException('Unable to open output stream.');
    }

    fprintf($out, "\xEF\xBB\xBF");

    fputcsv($out, ['member_id', 'name', 'surname', 'email', 'phone', 'branch_name', 'join_date', 'status'], ',', '"', '');
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['member_id'] ?? '',
            $row['name'] ?? '',
            $row['surname'] ?? '',
            $row['email'] ?? '',
            $row['phone'] ?? '',
            $row['branch_name'] ?? '',
            $row['join_date'] ?? '',
            $row['status'] ?? '',
        ], ',', '"', '');
    }

    fclose($out);
} catch (Throwable $e) {
    ApiResponse::error('Failed to export members.', 500, $e->getMessage());
}
