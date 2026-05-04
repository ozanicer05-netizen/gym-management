<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';
AuthGuard::requireApiAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405);
    exit;
}

if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
    ApiResponse::error('CSV file is required.', 422);
    exit;
}

$file = $_FILES['file'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    ApiResponse::error('Failed to upload file.', 422);
    exit;
}

$tmpName = (string) ($file['tmp_name'] ?? '');
if ($tmpName === '' || !is_uploaded_file($tmpName)) {
    ApiResponse::error('Invalid uploaded file.', 422);
    exit;
}

$handle = fopen($tmpName, 'r');
if ($handle === false) {
    ApiResponse::error('Unable to read CSV file.', 422);
    exit;
}

$conn = Database::connect();
$inserted = 0;
$skipped = 0;
$lineNo = 0;

$defaultPasswordHash = password_hash('Member123!', PASSWORD_BCRYPT);

try {
    while (($cols = fgetcsv($handle)) !== false) {
        $lineNo++;

        if ($lineNo === 1 && isset($cols[0]) && preg_match('/name|member_id/i', (string) $cols[0])) {
            continue;
        }

        $name = trim((string) ($cols[0] ?? ''));
        $surname = trim((string) ($cols[1] ?? ''));
        $email = trim((string) ($cols[2] ?? ''));
        $phone = trim((string) ($cols[3] ?? ''));
        $branchName = trim((string) ($cols[4] ?? ''));
        $status = strtolower(trim((string) ($cols[5] ?? 'active')));

        if ($name === '' || $surname === '' || $email === '') {
            $skipped++;
            continue;
        }

        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            $status = 'active';
        }

        $safeEmail = $conn->real_escape_string($email);
        $exists = $conn->query("SELECT user_id FROM users WHERE email = '{$safeEmail}' LIMIT 1");
        if ($exists && $exists->num_rows > 0) {
            $skipped++;
            continue;
        }

        $safeBranch = $conn->real_escape_string($branchName);
        $branchId = null;

        if ($safeBranch !== '') {
            $branchResult = $conn->query("SELECT branch_id FROM branches WHERE branch_name = '{$safeBranch}' LIMIT 1");
            if ($branchResult && $branchResult->num_rows > 0) {
                $branchId = (int) ($branchResult->fetch_assoc()['branch_id'] ?? 0);
            }
        }

        if (!$branchId) {
            $branchId = 1;
        }

        $stmtUser = $conn->prepare('INSERT INTO users (name, surname, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?, ?)');
        if ($stmtUser === false) {
            throw new RuntimeException('Unable to prepare user insert statement.');
        }

        $userStatus = $status === 'active' ? 'active' : 'inactive';
        $stmtUser->bind_param('ssssss', $name, $surname, $email, $phone, $defaultPasswordHash, $userStatus);
        $stmtUser->execute();
        $userId = (int) $stmtUser->insert_id;
        $stmtUser->close();

        $stmtRole = $conn->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, 3)');
        if ($stmtRole !== false) {
            $stmtRole->bind_param('i', $userId);
            $stmtRole->execute();
            $stmtRole->close();
        }

        $stmtMember = $conn->prepare('INSERT INTO members (user_id, branch_id, status) VALUES (?, ?, ?)');
        if ($stmtMember === false) {
            throw new RuntimeException('Unable to prepare member insert statement.');
        }

        $stmtMember->bind_param('iis', $userId, $branchId, $status);
        $stmtMember->execute();
        $stmtMember->close();

        $inserted++;
    }

    fclose($handle);

    ApiResponse::ok([
        'inserted' => $inserted,
        'skipped' => $skipped,
        'defaultPassword' => 'Member123!',
    ]);
} catch (Throwable $e) {
    fclose($handle);
    ApiResponse::error('Failed to import members.', 500, $e->getMessage());
}
