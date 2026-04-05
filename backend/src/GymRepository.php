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
        $limit = max(1, min(200, $limit));

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
            ORDER BY m.join_date DESC
            LIMIT {$limit}
        ";

        $result = $this->conn->query($sql);
        $members = [];

        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }

        return $members;
    }

    public function listTrainers(string $search = '', string $status = '', int $limit = 50): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit = max(1, min(200, $limit));

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
                t.availability_status
            FROM trainers t
            JOIN users u ON t.user_id = u.user_id
            {$where}
            ORDER BY t.trainer_id DESC
            LIMIT {$limit}
        ";

        $result = $this->conn->query($sql);
        $trainers = [];

        while ($row = $result->fetch_assoc()) {
            $trainers[] = $row;
        }

        return $trainers;
    }

    private function count(string $sql): int
    {
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();

        return (int) ($row['c'] ?? 0);
    }
}
