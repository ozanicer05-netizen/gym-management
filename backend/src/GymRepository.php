<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

final class GymRepository
{
    private mysqli $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    public function getDashboardStats(): array
    {
        return [
            'totalMembers' => $this->count("SELECT COUNT(*) AS c FROM members WHERE status='active'"),
            'totalTrainers' => $this->count("SELECT COUNT(*) AS c FROM trainers WHERE availability_status='active'"),
            'totalClasses' => $this->count('SELECT COUNT(*) AS c FROM classes'),
            'totalEquipment' => $this->count("SELECT COUNT(*) AS c FROM equipment WHERE status='active'"),
            'expiringSoon' => $this->count("SELECT COUNT(*) AS c FROM subscriptions WHERE status='active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"),
            'maintenanceDue' => $this->count("SELECT COUNT(*) AS c FROM equipment WHERE status='maintenance'"),
        ];
    }

    public function listMembers(string $search = '', string $status = '', int $limit = 50): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit = $this->normalizeLimit($limit);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND m.status = '{$safeStatus}'";
        }

        $sql = "
            SELECT
                m.member_id,
                u.name,
                u.surname,
                u.email,
                u.phone,
                b.branch_name,
                m.join_date,
                m.status
            FROM members m
            JOIN users u ON m.user_id = u.user_id
            JOIN branches b ON m.branch_id = b.branch_id
            {$where}
            ORDER BY m.member_id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function getMemberById(int $memberId): ?array
    {
        $memberId = (int) $memberId;

        $sql = "
            SELECT
                m.member_id,
                m.user_id,
                u.name,
                u.surname,
                u.email,
                u.phone,
                m.branch_id,
                b.branch_name,
                m.birth_date,
                m.gender,
                m.emergency_contact,
                m.join_date,
                m.status
            FROM members m
            JOIN users u ON m.user_id = u.user_id
            JOIN branches b ON m.branch_id = b.branch_id
            WHERE m.member_id = {$memberId}
            LIMIT 1
        ";

        $result = $this->conn->query($sql);
        $member = $result->fetch_assoc();

        return $member ?: null;
    }

    public function createMember(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $branchId = (int) ($data['branch_id'] ?? 0);
        $birthDate = $this->conn->real_escape_string((string) ($data['birth_date'] ?? ''));
        $gender = $this->conn->real_escape_string((string) ($data['gender'] ?? ''));
        $emergencyContact = $this->conn->real_escape_string((string) ($data['emergency_contact'] ?? ''));
        $status = $this->conn->real_escape_string((string) ($data['status'] ?? 'active'));

        if ($userId <= 0) {
            throw new InvalidArgumentException('user_id is required.');
        }

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        $birthDateValue = $birthDate !== '' ? "'{$birthDate}'" : "NULL";
        $genderValue = $gender !== '' ? "'{$gender}'" : "NULL";

        $sql = "
            INSERT INTO members
                (user_id, branch_id, birth_date, gender, emergency_contact, status)
            VALUES
                ({$userId}, {$branchId}, {$birthDateValue}, {$genderValue}, '{$emergencyContact}', '{$status}')
        ";

        $this->conn->query($sql);

        $memberId = (int) $this->conn->insert_id;

        return $this->getMemberById($memberId);
    }

    public function updateMember(int $memberId, array $data): array
    {
        $existingMember = $this->getMemberById($memberId);

        if (!$existingMember) {
            throw new InvalidArgumentException('Member not found.');
        }

        $branchId = (int) ($data['branch_id'] ?? $existingMember['branch_id']);
        $birthDate = $this->conn->real_escape_string((string) ($data['birth_date'] ?? $existingMember['birth_date']));
        $gender = $this->conn->real_escape_string((string) ($data['gender'] ?? $existingMember['gender']));
        $emergencyContact = $this->conn->real_escape_string((string) ($data['emergency_contact'] ?? $existingMember['emergency_contact']));
        $status = $this->conn->real_escape_string((string) ($data['status'] ?? $existingMember['status']));

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        $birthDateValue = $birthDate !== '' ? "'{$birthDate}'" : "NULL";
        $genderValue = $gender !== '' ? "'{$gender}'" : "NULL";

        $sql = "
            UPDATE members
            SET
                branch_id = {$branchId},
                birth_date = {$birthDateValue},
                gender = {$genderValue},
                emergency_contact = '{$emergencyContact}',
                status = '{$status}'
            WHERE member_id = {$memberId}
        ";

        $this->conn->query($sql);

        return $this->getMemberById($memberId);
    }

    public function deleteMember(int $memberId): void
    {
        $memberId = (int) $memberId;

        $existingMember = $this->getMemberById($memberId);

        if (!$existingMember) {
            throw new InvalidArgumentException('Member not found.');
        }

        $sql = "
            DELETE FROM members
            WHERE member_id = {$memberId}
        ";

        $this->conn->query($sql);
    }

    public function listTrainers(string $search = '', string $status = '', int $limit = 50): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit = $this->normalizeLimit($limit);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND t.availability_status = '{$safeStatus}'";
        }

        $sql = "
            SELECT
                t.trainer_id,
                u.name,
                u.surname,
                u.email,
                u.phone,
                b.branch_name,
                t.specialization,
                t.availability_status
            FROM trainers t
            JOIN users u ON t.user_id = u.user_id
            JOIN branches b ON t.branch_id = b.branch_id
            {$where}
            ORDER BY t.trainer_id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function listClasses(string $search = '', string $level = '', int $limit = 50): array
    {
        $search = trim($search);
        $level = trim($level);
        $limit = $this->normalizeLimit($limit);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeLevel = $this->conn->real_escape_string($level);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (c.class_name LIKE '%{$safeSearch}%' OR b.branch_name LIKE '%{$safeSearch}%' OR CONCAT(u.name, ' ', u.surname) LIKE '%{$safeSearch}%')";
        }
        if ($safeLevel !== '') {
            $where .= " AND c.level = '{$safeLevel}'";
        }

        $sql = "
            SELECT
                c.class_id,
                c.class_name,
                c.capacity,
                c.duration_min,
                c.level,
                b.branch_name,
                CONCAT(u.name, ' ', u.surname) AS trainer_name
            FROM classes c
            JOIN trainers t ON c.trainer_id = t.trainer_id
            JOIN users u ON t.user_id = u.user_id
            JOIN branches b ON c.branch_id = b.branch_id
            {$where}
            ORDER BY c.class_id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function listBranches(string $search = '', string $status = '', int $limit = 50): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit = $this->normalizeLimit($limit);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (branch_name LIKE '%{$safeSearch}%' OR city LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND status = '{$safeStatus}'";
        }

        $sql = "
            SELECT
                branch_id,
                branch_name,
                city,
                phone,
                status
            FROM branches
            {$where}
            ORDER BY branch_id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function listSubscriptions(string $search = '', string $status = '', int $limit = 50): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit = $this->normalizeLimit($limit);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (CONCAT(u.name, ' ', u.surname) LIKE '%{$safeSearch}%' OR p.package_name LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND s.status = '{$safeStatus}'";
        }

        $sql = "
            SELECT
                s.subscription_id,
                CONCAT(u.name, ' ', u.surname) AS member_name,
                p.package_name,
                s.start_date,
                s.end_date,
                s.status
            FROM subscriptions s
            JOIN members m ON s.member_id = m.member_id
            JOIN users u ON m.user_id = u.user_id
            JOIN packages p ON s.package_id = p.package_id
            {$where}
            ORDER BY s.subscription_id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function listEquipment(string $search = '', string $status = '', int $limit = 50): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit = $this->normalizeLimit($limit);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (e.equipment_name LIKE '%{$safeSearch}%' OR e.brand LIKE '%{$safeSearch}%' OR b.branch_name LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND e.status = '{$safeStatus}'";
        }

        $sql = "
            SELECT
                e.equipment_id,
                e.equipment_name,
                e.brand,
                e.purchase_date,
                e.status,
                b.branch_name,
                ec.category_name
            FROM equipment e
            JOIN branches b ON e.branch_id = b.branch_id
            JOIN equipment_categories ec ON e.category_id = ec.category_id
            {$where}
            ORDER BY e.equipment_id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    private function normalizeLimit(int $limit): int
    {
        return max(1, min(200, $limit));
    }

    private function count(string $sql): int
    {
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();

        return (int) ($row['c'] ?? 0);
    }

    private function fetchAll(string $sql): array
    {
        $result = $this->conn->query($sql);
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }
}
