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

    // -------------------------------------------------------------------------
    // DASHBOARD
    // -------------------------------------------------------------------------

    public function getDashboardStats(): array
    {
        return [
            'totalMembers'            => $this->count("SELECT COUNT(*) AS c FROM members WHERE status='active'"),
            'inactiveMembers'         => $this->count("SELECT COUNT(*) AS c FROM members WHERE status IN ('inactive','suspended')"),
            'totalTrainers'           => $this->count("SELECT COUNT(*) AS c FROM trainers WHERE availability_status='active'"),
            'totalClasses'            => $this->count('SELECT COUNT(*) AS c FROM classes'),
            'totalEquipment'          => $this->count("SELECT COUNT(*) AS c FROM equipment WHERE status='active'"),
            'expiringSoon'            => $this->count("SELECT COUNT(*) AS c FROM subscriptions WHERE status='active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"),
            'maintenanceDue'          => $this->count("SELECT COUNT(*) AS c FROM equipment WHERE status='maintenance'"),
            'cityDistribution'        => $this->getCityDistribution(6),
            'topBranches'             => $this->getTopBranches(6),
            'monthlyRevenue'          => $this->getMonthlyRevenue(),
            'subscriptionBreakdown'   => $this->getSubscriptionBreakdown(),
            'recentActivity'          => $this->getRecentActivity(8),
            'monthlyRevenueChart'     => $this->getMonthlyRevenueChart(6),
            'branchRevenue'           => $this->getBranchRevenue(6),
            'packageProfitability'    => $this->getPackageProfitability(6),
        ];
    }

    public function getMonthlyRevenue(): float
    {
        $sql = "
            SELECT COALESCE(SUM(amount), 0) AS c
            FROM payments
            WHERE status = 'paid'
                            AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ";
        $result = $this->conn->query($sql);
        $row    = $result->fetch_assoc();

        return (float) ($row['c'] ?? 0);
    }

    public function getSubscriptionBreakdown(): array
    {
        $sql = "
            SELECT status AS label, COUNT(*) AS value
            FROM subscriptions
            GROUP BY status
            ORDER BY FIELD(status, 'active', 'expired', 'cancelled')
        ";

        return $this->fetchAll($sql);
    }

    public function getRecentActivity(int $limit = 8): array
    {
        $limit = $this->normalizeLimit($limit);
        $sql = "
            (
                SELECT
                    'New Member' AS type,
                    CONCAT(u.name, ' ', u.surname) AS subject,
                    b.branch_name AS detail,
                    m.join_date AS happened_at
                FROM members m
                JOIN users u ON m.user_id = u.user_id
                JOIN branches b ON m.branch_id = b.branch_id
                ORDER BY m.join_date DESC
                LIMIT {$limit}
            )
            UNION ALL
            (
                SELECT
                    'New Subscription' AS type,
                    CONCAT(u.name, ' ', u.surname) AS subject,
                    p.package_name AS detail,
                    CONCAT(s.start_date, ' 00:00:00') AS happened_at
                FROM subscriptions s
                JOIN members m ON s.member_id = m.member_id
                JOIN users u ON m.user_id = u.user_id
                JOIN packages p ON s.package_id = p.package_id
                ORDER BY s.subscription_id DESC
                LIMIT {$limit}
            )
            UNION ALL
            (
                SELECT
                    'Payment Received' AS type,
                    CONCAT(u.name, ' ', u.surname) AS subject,
                    CONCAT('$', FORMAT(py.amount, 2)) AS detail,
                    py.paid_at AS happened_at
                FROM payments py
                JOIN subscriptions s ON py.subscription_id = s.subscription_id
                JOIN members m ON s.member_id = m.member_id
                JOIN users u ON m.user_id = u.user_id
                WHERE py.status = 'paid' AND py.paid_at IS NOT NULL
                ORDER BY py.paid_at DESC
                LIMIT {$limit}
            )
            ORDER BY happened_at DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function getMonthlyRevenueChart(int $months = 6): array
    {
        $months = max(1, min(24, $months));
        $sql = "
            SELECT
                DATE_FORMAT(paid_at, '%Y-%m') AS label,
                COALESCE(SUM(amount), 0) AS value
            FROM payments
            WHERE status = 'paid'
              AND paid_at >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)
            GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
            ORDER BY label ASC
        ";

        return $this->fetchAll($sql);
    }

    public function getBranchRevenue(int $limit = 6): array
    {
        $limit = $this->normalizeLimit($limit);
        $sql = "
            SELECT
                b.branch_name AS label,
                COALESCE(SUM(py.amount), 0) AS value
            FROM branches b
            LEFT JOIN members m ON m.branch_id = b.branch_id
            LEFT JOIN subscriptions s ON s.member_id = m.member_id
            LEFT JOIN payments py ON py.subscription_id = s.subscription_id
                AND py.status = 'paid'
                AND py.paid_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            WHERE b.status = 'active'
            GROUP BY b.branch_id, b.branch_name
            ORDER BY value DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function getPackageProfitability(int $limit = 6): array
    {
        $limit = $this->normalizeLimit($limit);
        $sql = "
            SELECT
                p.package_name AS label,
                COUNT(DISTINCT s.subscription_id) AS subscription_count,
                COALESCE(SUM(py.amount), 0) AS total_revenue,
                p.price AS unit_price
            FROM packages p
            LEFT JOIN subscriptions s ON s.package_id = p.package_id
            LEFT JOIN payments py ON py.subscription_id = s.subscription_id
                AND py.status = 'paid'
            WHERE p.is_active = 1
            GROUP BY p.package_id, p.package_name, p.price
            ORDER BY total_revenue DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function getCityDistribution(int $limit = 6): array
    {
        $limit = $this->normalizeLimit($limit);
        $sql = "
            SELECT
                b.city AS label,
                COUNT(m.member_id) AS value
            FROM members m
            JOIN branches b ON b.branch_id = m.branch_id
            WHERE m.status = 'active'
            GROUP BY b.city
            ORDER BY value DESC, b.city ASC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function getTopBranches(int $limit = 6): array
    {
        $limit = $this->normalizeLimit($limit);
        $sql = "
            SELECT
                b.branch_name AS label,
                COUNT(m.member_id) AS value
            FROM members m
            JOIN branches b ON b.branch_id = m.branch_id
            WHERE m.status = 'active'
            GROUP BY b.branch_id, b.branch_name
            ORDER BY value DESC, b.branch_name ASC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    // -------------------------------------------------------------------------
    // MEMBERS
    // -------------------------------------------------------------------------

    public function listMembers(string $search = '', string $status = '', int $limit = 50, int $offset = 0, ?int $branchId = null): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit  = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND m.status = '{$safeStatus}'";
        }
        if ($branchId !== null) {
            $where .= " AND m.branch_id = " . (int) $branchId;
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
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->fetchAll($sql);
    }

    public function countMembers(string $search = '', string $status = '', ?int $branchId = null): int
    {
        $search = trim($search);
        $status = trim($status);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND m.status = '{$safeStatus}'";
        }
        if ($branchId !== null) {
            $where .= " AND m.branch_id = " . (int) $branchId;
        }

        $sql = "
            SELECT COUNT(*) AS c
            FROM members m
            JOIN users u ON m.user_id = u.user_id
            {$where}
        ";

        return $this->count($sql);
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
        $userId           = (int) ($data['user_id'] ?? 0);
        $branchId         = (int) ($data['branch_id'] ?? 0);
        $birthDate        = $this->conn->real_escape_string((string) ($data['birth_date'] ?? ''));
        $gender           = $this->conn->real_escape_string((string) ($data['gender'] ?? ''));
        $emergencyContact = $this->conn->real_escape_string((string) ($data['emergency_contact'] ?? ''));
        $status           = $this->conn->real_escape_string((string) ($data['status'] ?? 'active'));

        if ($userId <= 0 && !empty($data['user'])) {
            $userId = $this->createUser((array) $data['user']);
        }

        if ($userId <= 0) {
            throw new InvalidArgumentException('user_id is required.');
        }

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        $birthDateValue = $birthDate !== '' ? "'{$birthDate}'" : 'NULL';
        $genderValue    = $gender !== ''    ? "'{$gender}'"    : 'NULL';

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

        $branchId         = (int) ($data['branch_id'] ?? $existingMember['branch_id']);
        $birthDate        = $this->conn->real_escape_string((string) ($data['birth_date'] ?? $existingMember['birth_date']));
        $gender           = $this->conn->real_escape_string((string) ($data['gender'] ?? $existingMember['gender']));
        $emergencyContact = $this->conn->real_escape_string((string) ($data['emergency_contact'] ?? $existingMember['emergency_contact']));
        $status           = $this->conn->real_escape_string((string) ($data['status'] ?? $existingMember['status']));

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        $birthDateValue = $birthDate !== '' ? "'{$birthDate}'" : 'NULL';
        $genderValue    = $gender !== ''    ? "'{$gender}'"    : 'NULL';

        $sql = "
            UPDATE members
            SET
                branch_id         = {$branchId},
                birth_date        = {$birthDateValue},
                gender            = {$genderValue},
                emergency_contact = '{$emergencyContact}',
                status            = '{$status}'
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

        $this->conn->query("DELETE FROM members WHERE member_id = {$memberId}");
    }

    // -------------------------------------------------------------------------
    // TRAINERS
    // -------------------------------------------------------------------------

    public function listTrainers(string $search = '', string $status = '', int $limit = 50, int $offset = 0, ?int $branchId = null): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit  = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND t.availability_status = '{$safeStatus}'";
        }
        if ($branchId !== null) {
            $where .= " AND t.branch_id = " . (int) $branchId;
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
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->fetchAll($sql);
    }

    public function countTrainers(string $search = '', string $status = '', ?int $branchId = null): int
    {
        $search = trim($search);
        $status = trim($status);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND t.availability_status = '{$safeStatus}'";
        }
        if ($branchId !== null) {
            $where .= " AND t.branch_id = " . (int) $branchId;
        }

        $sql = "
            SELECT COUNT(*) AS c
            FROM trainers t
            JOIN users u ON t.user_id = u.user_id
            {$where}
        ";

        return $this->count($sql);
    }

    public function getTrainerById(int $trainerId): ?array
    {
        $trainerId = (int) $trainerId;

        $sql = "
            SELECT
                t.trainer_id,
                t.user_id,
                u.name,
                u.surname,
                u.email,
                u.phone,
                t.branch_id,
                b.branch_name,
                t.specialization,
                t.availability_status
            FROM trainers t
            JOIN users u ON t.user_id = u.user_id
            JOIN branches b ON t.branch_id = b.branch_id
            WHERE t.trainer_id = {$trainerId}
            LIMIT 1
        ";

        $result  = $this->conn->query($sql);
        $trainer = $result->fetch_assoc();

        return $trainer ?: null;
    }

    public function createTrainer(array $data): array
    {
        $userId             = (int) ($data['user_id'] ?? 0);
        $branchId           = (int) ($data['branch_id'] ?? 0);
        $specialization     = $this->conn->real_escape_string(trim((string) ($data['specialization'] ?? '')));
        $availabilityStatus = $this->conn->real_escape_string(trim((string) ($data['availability_status'] ?? 'active')));

        if ($userId <= 0 && !empty($data['user'])) {
            $userId = $this->createUser((array) $data['user']);
        }

        if ($userId <= 0) {
            throw new InvalidArgumentException('user_id is required.');
        }

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        $sql = "
            INSERT INTO trainers
                (user_id, branch_id, specialization, availability_status)
            VALUES
                ({$userId}, {$branchId}, '{$specialization}', '{$availabilityStatus}')
        ";

        $this->conn->query($sql);

        $trainerId = (int) $this->conn->insert_id;

        return $this->getTrainerById($trainerId);
    }

    public function updateTrainer(int $trainerId, array $data): array
    {
        $existingTrainer = $this->getTrainerById($trainerId);

        if (!$existingTrainer) {
            throw new InvalidArgumentException('Trainer not found.');
        }

        $branchId           = (int) ($data['branch_id'] ?? $existingTrainer['branch_id']);
        $specialization     = $this->conn->real_escape_string(trim((string) ($data['specialization'] ?? $existingTrainer['specialization'])));
        $availabilityStatus = $this->conn->real_escape_string(trim((string) ($data['availability_status'] ?? $existingTrainer['availability_status'])));

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        $sql = "
            UPDATE trainers
            SET
                branch_id           = {$branchId},
                specialization      = '{$specialization}',
                availability_status = '{$availabilityStatus}'
            WHERE trainer_id = {$trainerId}
        ";

        $this->conn->query($sql);

        return $this->getTrainerById($trainerId);
    }

    public function deleteTrainer(int $trainerId): void
    {
        $trainerId = (int) $trainerId;

        $existingTrainer = $this->getTrainerById($trainerId);

        if (!$existingTrainer) {
            throw new InvalidArgumentException('Trainer not found.');
        }

        $this->conn->query("DELETE FROM trainers WHERE trainer_id = {$trainerId}");
    }

    // -------------------------------------------------------------------------
    // CLASSES
    // -------------------------------------------------------------------------

    public function listClasses(string $search = '', string $level = '', int $limit = 50, int $offset = 0): array
    {
        $search = trim($search);
        $level  = trim($level);
        $limit  = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeLevel  = $this->conn->real_escape_string($level);

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
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->fetchAll($sql);
    }

    public function countClasses(string $search = '', string $level = ''): int
    {
        $search = trim($search);
        $level  = trim($level);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeLevel  = $this->conn->real_escape_string($level);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (c.class_name LIKE '%{$safeSearch}%' OR b.branch_name LIKE '%{$safeSearch}%' OR CONCAT(u.name, ' ', u.surname) LIKE '%{$safeSearch}%')";
        }
        if ($safeLevel !== '') {
            $where .= " AND c.level = '{$safeLevel}'";
        }

        $sql = "
            SELECT COUNT(*) AS c
            FROM classes c
            JOIN trainers t ON c.trainer_id = t.trainer_id
            JOIN users u ON t.user_id = u.user_id
            JOIN branches b ON c.branch_id = b.branch_id
            {$where}
        ";

        return $this->count($sql);
    }

    public function getClassById(int $classId): ?array
    {
        $classId = (int) $classId;

        $sql = "
            SELECT
                c.class_id,
                c.trainer_id,
                c.branch_id,
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
            WHERE c.class_id = {$classId}
            LIMIT 1
        ";

        $result = $this->conn->query($sql);
        $class  = $result->fetch_assoc();

        return $class ?: null;
    }

    public function createClass(array $data): array
    {
        $trainerId   = (int) ($data['trainer_id'] ?? 0);
        $branchId    = (int) ($data['branch_id'] ?? 0);
        $className   = $this->conn->real_escape_string(trim((string) ($data['class_name'] ?? '')));
        $capacity    = (int) ($data['capacity'] ?? 20);
        $durationMin = (int) ($data['duration_min'] ?? 60);
        $level       = $this->conn->real_escape_string(trim((string) ($data['level'] ?? 'beginner')));

        if ($trainerId <= 0) {
            throw new InvalidArgumentException('trainer_id is required.');
        }

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        if ($className === '') {
            throw new InvalidArgumentException('class_name is required.');
        }

        if ($capacity <= 0) {
            throw new InvalidArgumentException('capacity must be greater than 0.');
        }

        if ($durationMin <= 0) {
            throw new InvalidArgumentException('duration_min must be greater than 0.');
        }

        $sql = "
            INSERT INTO classes
                (trainer_id, branch_id, class_name, capacity, duration_min, level)
            VALUES
                ({$trainerId}, {$branchId}, '{$className}', {$capacity}, {$durationMin}, '{$level}')
        ";

        $this->conn->query($sql);

        $classId = (int) $this->conn->insert_id;

        return $this->getClassById($classId);
    }

    public function updateClass(int $classId, array $data): array
    {
        $existingClass = $this->getClassById($classId);

        if (!$existingClass) {
            throw new InvalidArgumentException('Class not found.');
        }

        $trainerId   = (int) ($data['trainer_id'] ?? $existingClass['trainer_id']);
        $branchId    = (int) ($data['branch_id'] ?? $existingClass['branch_id']);
        $className   = $this->conn->real_escape_string(trim((string) ($data['class_name'] ?? $existingClass['class_name'])));
        $capacity    = (int) ($data['capacity'] ?? $existingClass['capacity']);
        $durationMin = (int) ($data['duration_min'] ?? $existingClass['duration_min']);
        $level       = $this->conn->real_escape_string(trim((string) ($data['level'] ?? $existingClass['level'])));

        if ($trainerId <= 0) {
            throw new InvalidArgumentException('trainer_id is required.');
        }

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        if ($className === '') {
            throw new InvalidArgumentException('class_name is required.');
        }

        if ($capacity <= 0) {
            throw new InvalidArgumentException('capacity must be greater than 0.');
        }

        if ($durationMin <= 0) {
            throw new InvalidArgumentException('duration_min must be greater than 0.');
        }

        $sql = "
            UPDATE classes
            SET
                trainer_id   = {$trainerId},
                branch_id    = {$branchId},
                class_name   = '{$className}',
                capacity     = {$capacity},
                duration_min = {$durationMin},
                level        = '{$level}'
            WHERE class_id = {$classId}
        ";

        $this->conn->query($sql);

        return $this->getClassById($classId);
    }

    public function deleteClass(int $classId): void
    {
        $classId = (int) $classId;

        $existingClass = $this->getClassById($classId);

        if (!$existingClass) {
            throw new InvalidArgumentException('Class not found.');
        }

        $this->conn->query("DELETE FROM classes WHERE class_id = {$classId}");
    }

    // -------------------------------------------------------------------------
    // BRANCHES
    // -------------------------------------------------------------------------

    public function listBranches(string $search = '', string $status = '', int $limit = 50, int $offset = 0, bool $withStats = false): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit  = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (b.branch_name LIKE '%{$safeSearch}%' OR b.city LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND b.status = '{$safeStatus}'";
        }

        if ($withStats) {
            $sql = "
                SELECT
                    b.branch_id,
                    b.branch_name,
                    b.city,
                    b.phone,
                    b.status,
                    COUNT(DISTINCT m.member_id) AS member_count,
                    COALESCE(SUM(CASE
                        WHEN py.status = 'paid'
                         AND py.paid_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        THEN py.amount ELSE 0 END), 0) AS monthly_revenue
                FROM branches b
                LEFT JOIN members m ON m.branch_id = b.branch_id AND m.status = 'active'
                LEFT JOIN subscriptions s ON s.member_id = m.member_id
                LEFT JOIN payments py ON py.subscription_id = s.subscription_id
                {$where}
                GROUP BY b.branch_id, b.branch_name, b.city, b.phone, b.status
                ORDER BY monthly_revenue DESC
                LIMIT {$limit} OFFSET {$offset}
            ";
        } else {
            $sql = "
                SELECT
                    branch_id,
                    branch_name,
                    city,
                    phone,
                    status
                FROM branches b
                {$where}
                ORDER BY branch_id DESC
                LIMIT {$limit} OFFSET {$offset}
            ";
        }

        return $this->fetchAll($sql);
    }

    public function countBranches(string $search = '', string $status = ''): int
    {
        $search = trim($search);
        $status = trim($status);

        $safeSearch = $this->conn->real_escape_string($search);
        $safeStatus = $this->conn->real_escape_string($status);

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (branch_name LIKE '%{$safeSearch}%' OR city LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND status = '{$safeStatus}'";
        }

        $sql = "SELECT COUNT(*) AS c FROM branches {$where}";

        return $this->count($sql);
    }

    public function getBranchById(int $branchId): ?array
    {
        $branchId = (int) $branchId;

        $sql = "
            SELECT
                branch_id,
                branch_name,
                address,
                city,
                phone,
                status
            FROM branches
            WHERE branch_id = {$branchId}
            LIMIT 1
        ";

        $result = $this->conn->query($sql);
        $branch = $result->fetch_assoc();

        return $branch ?: null;
    }

    public function createBranch(array $data): array
    {
        $branchName = $this->conn->real_escape_string(trim((string) ($data['branch_name'] ?? '')));
        $address    = $this->conn->real_escape_string(trim((string) ($data['address'] ?? '')));
        $city       = $this->conn->real_escape_string(trim((string) ($data['city'] ?? '')));
        $phone      = $this->conn->real_escape_string(trim((string) ($data['phone'] ?? '')));
        $status     = $this->conn->real_escape_string(trim((string) ($data['status'] ?? 'active')));

        if ($branchName === '') {
            throw new InvalidArgumentException('branch_name is required.');
        }

        $sql = "
            INSERT INTO branches
                (branch_name, address, city, phone, status)
            VALUES
                ('{$branchName}', '{$address}', '{$city}', '{$phone}', '{$status}')
        ";

        $this->conn->query($sql);

        $branchId = (int) $this->conn->insert_id;

        return $this->getBranchById($branchId);
    }

    public function updateBranch(int $branchId, array $data): array
    {
        $existingBranch = $this->getBranchById($branchId);

        if (!$existingBranch) {
            throw new InvalidArgumentException('Branch not found.');
        }

        $branchName = $this->conn->real_escape_string(trim((string) ($data['branch_name'] ?? $existingBranch['branch_name'])));
        $address    = $this->conn->real_escape_string(trim((string) ($data['address'] ?? $existingBranch['address'])));
        $city       = $this->conn->real_escape_string(trim((string) ($data['city'] ?? $existingBranch['city'])));
        $phone      = $this->conn->real_escape_string(trim((string) ($data['phone'] ?? $existingBranch['phone'])));
        $status     = $this->conn->real_escape_string(trim((string) ($data['status'] ?? $existingBranch['status'])));

        if ($branchName === '') {
            throw new InvalidArgumentException('branch_name is required.');
        }

        $sql = "
            UPDATE branches
            SET
                branch_name = '{$branchName}',
                address     = '{$address}',
                city        = '{$city}',
                phone       = '{$phone}',
                status      = '{$status}'
            WHERE branch_id = {$branchId}
        ";

        $this->conn->query($sql);

        return $this->getBranchById($branchId);
    }

    public function deleteBranch(int $branchId): void
    {
        $branchId = (int) $branchId;

        $existingBranch = $this->getBranchById($branchId);

        if (!$existingBranch) {
            throw new InvalidArgumentException('Branch not found.');
        }

        $this->conn->query("DELETE FROM branches WHERE branch_id = {$branchId}");
    }

    // -------------------------------------------------------------------------
    // SUBSCRIPTIONS
    // -------------------------------------------------------------------------

    public function listSubscriptions(string $search = '', string $status = '', int $limit = 50, int $offset = 0): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit  = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);

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
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->fetchAll($sql);
    }

    public function countSubscriptions(string $search = '', string $status = ''): int
    {
        $search = trim($search);
        $status = trim($status);

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
            SELECT COUNT(*) AS c
            FROM subscriptions s
            JOIN members m ON s.member_id = m.member_id
            JOIN users u ON m.user_id = u.user_id
            JOIN packages p ON s.package_id = p.package_id
            {$where}
        ";

        return $this->count($sql);
    }

    public function getSubscriptionById(int $subscriptionId): ?array
    {
        $subscriptionId = (int) $subscriptionId;

        $sql = "
            SELECT
                s.subscription_id,
                s.member_id,
                s.package_id,
                CONCAT(u.name, ' ', u.surname) AS member_name,
                p.package_name,
                s.start_date,
                s.end_date,
                s.status
            FROM subscriptions s
            JOIN members m ON s.member_id = m.member_id
            JOIN users u ON m.user_id = u.user_id
            JOIN packages p ON s.package_id = p.package_id
            WHERE s.subscription_id = {$subscriptionId}
            LIMIT 1
        ";

        $result       = $this->conn->query($sql);
        $subscription = $result->fetch_assoc();

        return $subscription ?: null;
    }

    public function createSubscription(array $data): array
    {
        $memberId  = (int) ($data['member_id'] ?? 0);
        $packageId = (int) ($data['package_id'] ?? 0);
        $startDate = $this->conn->real_escape_string(trim((string) ($data['start_date'] ?? '')));
        $endDate   = $this->conn->real_escape_string(trim((string) ($data['end_date'] ?? '')));
        $status    = $this->conn->real_escape_string(trim((string) ($data['status'] ?? 'active')));

        if ($memberId <= 0) {
            throw new InvalidArgumentException('member_id is required.');
        }

        if ($packageId <= 0) {
            throw new InvalidArgumentException('package_id is required.');
        }

        if ($startDate === '') {
            throw new InvalidArgumentException('start_date is required.');
        }

        if ($endDate === '') {
            throw new InvalidArgumentException('end_date is required.');
        }

        $sql = "
            INSERT INTO subscriptions
                (member_id, package_id, start_date, end_date, status)
            VALUES
                ({$memberId}, {$packageId}, '{$startDate}', '{$endDate}', '{$status}')
        ";

        $this->conn->query($sql);

        $subscriptionId = (int) $this->conn->insert_id;

        return $this->getSubscriptionById($subscriptionId);
    }

    public function updateSubscription(int $subscriptionId, array $data): array
    {
        $existingSubscription = $this->getSubscriptionById($subscriptionId);

        if (!$existingSubscription) {
            throw new InvalidArgumentException('Subscription not found.');
        }

        $memberId  = (int) ($data['member_id'] ?? $existingSubscription['member_id']);
        $packageId = (int) ($data['package_id'] ?? $existingSubscription['package_id']);
        $startDate = $this->conn->real_escape_string(trim((string) ($data['start_date'] ?? $existingSubscription['start_date'])));
        $endDate   = $this->conn->real_escape_string(trim((string) ($data['end_date'] ?? $existingSubscription['end_date'])));
        $status    = $this->conn->real_escape_string(trim((string) ($data['status'] ?? $existingSubscription['status'])));

        if ($memberId <= 0) {
            throw new InvalidArgumentException('member_id is required.');
        }

        if ($packageId <= 0) {
            throw new InvalidArgumentException('package_id is required.');
        }

        if ($startDate === '') {
            throw new InvalidArgumentException('start_date is required.');
        }

        if ($endDate === '') {
            throw new InvalidArgumentException('end_date is required.');
        }

        $sql = "
            UPDATE subscriptions
            SET
                member_id  = {$memberId},
                package_id = {$packageId},
                start_date = '{$startDate}',
                end_date   = '{$endDate}',
                status     = '{$status}'
            WHERE subscription_id = {$subscriptionId}
        ";

        $this->conn->query($sql);

        return $this->getSubscriptionById($subscriptionId);
    }

    public function deleteSubscription(int $subscriptionId): void
    {
        $subscriptionId = (int) $subscriptionId;

        $existingSubscription = $this->getSubscriptionById($subscriptionId);

        if (!$existingSubscription) {
            throw new InvalidArgumentException('Subscription not found.');
        }

        $this->conn->query("DELETE FROM subscriptions WHERE subscription_id = {$subscriptionId}");
    }

    // -------------------------------------------------------------------------
    // EQUIPMENT
    // -------------------------------------------------------------------------

    public function listEquipment(string $search = '', string $status = '', int $limit = 50, int $offset = 0): array
    {
        $search = trim($search);
        $status = trim($status);
        $limit  = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);

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
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->fetchAll($sql);
    }

    public function countEquipment(string $search = '', string $status = ''): int
    {
        $search = trim($search);
        $status = trim($status);

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
            SELECT COUNT(*) AS c
            FROM equipment e
            JOIN branches b ON e.branch_id = b.branch_id
            {$where}
        ";

        return $this->count($sql);
    }

    public function getEquipmentById(int $equipmentId): ?array
    {
        $equipmentId = (int) $equipmentId;

        $sql = "
            SELECT
                e.equipment_id,
                e.branch_id,
                e.category_id,
                e.equipment_name,
                e.brand,
                e.purchase_date,
                e.status,
                b.branch_name,
                ec.category_name
            FROM equipment e
            JOIN branches b ON e.branch_id = b.branch_id
            JOIN equipment_categories ec ON e.category_id = ec.category_id
            WHERE e.equipment_id = {$equipmentId}
            LIMIT 1
        ";

        $result    = $this->conn->query($sql);
        $equipment = $result->fetch_assoc();

        return $equipment ?: null;
    }

    public function createEquipment(array $data): array
    {
        $branchId      = (int) ($data['branch_id'] ?? 0);
        $categoryId    = (int) ($data['category_id'] ?? 0);
        $equipmentName = $this->conn->real_escape_string(trim((string) ($data['equipment_name'] ?? '')));
        $brand         = $this->conn->real_escape_string(trim((string) ($data['brand'] ?? '')));
        $purchaseDate  = $this->conn->real_escape_string(trim((string) ($data['purchase_date'] ?? '')));
        $status        = $this->conn->real_escape_string(trim((string) ($data['status'] ?? 'active')));

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        if ($categoryId <= 0) {
            throw new InvalidArgumentException('category_id is required.');
        }

        if ($equipmentName === '') {
            throw new InvalidArgumentException('equipment_name is required.');
        }

        $purchaseDateValue = $purchaseDate !== '' ? "'{$purchaseDate}'" : 'NULL';

        $sql = "
            INSERT INTO equipment
                (branch_id, category_id, equipment_name, brand, purchase_date, status)
            VALUES
                ({$branchId}, {$categoryId}, '{$equipmentName}', '{$brand}', {$purchaseDateValue}, '{$status}')
        ";

        $this->conn->query($sql);

        $equipmentId = (int) $this->conn->insert_id;

        return $this->getEquipmentById($equipmentId);
    }

    public function updateEquipment(int $equipmentId, array $data): array
    {
        $existingEquipment = $this->getEquipmentById($equipmentId);

        if (!$existingEquipment) {
            throw new InvalidArgumentException('Equipment not found.');
        }

        $branchId      = (int) ($data['branch_id'] ?? $existingEquipment['branch_id']);
        $categoryId    = (int) ($data['category_id'] ?? $existingEquipment['category_id']);
        $equipmentName = $this->conn->real_escape_string(trim((string) ($data['equipment_name'] ?? $existingEquipment['equipment_name'])));
        $brand         = $this->conn->real_escape_string(trim((string) ($data['brand'] ?? $existingEquipment['brand'])));
        $purchaseDate  = $this->conn->real_escape_string(trim((string) ($data['purchase_date'] ?? $existingEquipment['purchase_date'])));
        $status        = $this->conn->real_escape_string(trim((string) ($data['status'] ?? $existingEquipment['status'])));

        if ($branchId <= 0) {
            throw new InvalidArgumentException('branch_id is required.');
        }

        if ($categoryId <= 0) {
            throw new InvalidArgumentException('category_id is required.');
        }

        if ($equipmentName === '') {
            throw new InvalidArgumentException('equipment_name is required.');
        }

        $purchaseDateValue = $purchaseDate !== '' ? "'{$purchaseDate}'" : 'NULL';

        $sql = "
            UPDATE equipment
            SET
                branch_id      = {$branchId},
                category_id    = {$categoryId},
                equipment_name = '{$equipmentName}',
                brand          = '{$brand}',
                purchase_date  = {$purchaseDateValue},
                status         = '{$status}'
            WHERE equipment_id = {$equipmentId}
        ";

        $this->conn->query($sql);

        return $this->getEquipmentById($equipmentId);
    }

    public function deleteEquipment(int $equipmentId): void
    {
        $equipmentId = (int) $equipmentId;

        $existingEquipment = $this->getEquipmentById($equipmentId);

        if (!$existingEquipment) {
            throw new InvalidArgumentException('Equipment not found.');
        }

        $this->conn->query("DELETE FROM equipment WHERE equipment_id = {$equipmentId}");
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    public function listUsers(string $search = '', string $status = '', int $limit = 50, int $offset = 0): array
    {
        $limit  = $this->normalizeLimit($limit);
        $offset = $this->normalizeOffset($offset);
        $safeSearch = $this->conn->real_escape_string(trim($search));
        $safeStatus = $this->conn->real_escape_string(trim($status));

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND u.status = '{$safeStatus}'";
        }

        $sql = "
            SELECT
                u.user_id,
                u.name,
                u.surname,
                u.email,
                u.phone,
                u.status,
                COALESCE(GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_name SEPARATOR ', '), '') AS roles,
                CASE WHEN m.member_id IS NULL THEN 0 ELSE 1 END AS is_member,
                CASE WHEN t.trainer_id IS NULL THEN 0 ELSE 1 END AS is_trainer
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.user_id
            LEFT JOIN roles r ON r.role_id = ur.role_id
            LEFT JOIN members m ON m.user_id = u.user_id
            LEFT JOIN trainers t ON t.user_id = u.user_id
            {$where}
            GROUP BY u.user_id
            ORDER BY u.user_id DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->fetchAll($sql);
    }

    public function countUsers(string $search = '', string $status = ''): int
    {
        $safeSearch = $this->conn->real_escape_string(trim($search));
        $safeStatus = $this->conn->real_escape_string(trim($status));

        $where = 'WHERE 1=1';
        if ($safeSearch !== '') {
            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";
        }
        if ($safeStatus !== '') {
            $where .= " AND u.status = '{$safeStatus}'";
        }

        return $this->count("SELECT COUNT(*) AS c FROM users u {$where}");
    }

    public function getUserById(int $userId): ?array
    {
        $userId = (int) $userId;
        $result = $this->conn->query("
            SELECT u.user_id, u.name, u.surname, u.email, u.phone, u.status
            FROM users u WHERE u.user_id = {$userId} LIMIT 1
        ");
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    public function updateUser(int $userId, array $data): array
    {
        $existing = $this->getUserById($userId);
        if (!$existing) {
            throw new InvalidArgumentException('User not found.');
        }

        $name    = $this->conn->real_escape_string((string) ($data['name']    ?? $existing['name']));
        $surname = $this->conn->real_escape_string((string) ($data['surname'] ?? $existing['surname']));
        $phone   = $this->conn->real_escape_string((string) ($data['phone']   ?? $existing['phone']));
        $status  = $this->conn->real_escape_string((string) ($data['status']  ?? $existing['status']));

        $this->conn->query("
            UPDATE users
            SET name = '{$name}', surname = '{$surname}', phone = '{$phone}', status = '{$status}'
            WHERE user_id = {$userId}
        ");

        return $this->getUserById($userId);
    }

    public function deleteUser(int $userId): void
    {
        $userId = (int) $userId;
        $existing = $this->getUserById($userId);
        if (!$existing) {
            throw new InvalidArgumentException('User not found.');
        }
        $this->conn->query("DELETE FROM users WHERE user_id = {$userId}");
    }

    public function createUserOnly(array $data): array
    {
        $userId = $this->createUser($data);
        return $this->getUserById($userId);
    }

    private function createUser(array $data): int
    {
        $name     = trim((string) ($data['name'] ?? ''));
        $surname  = trim((string) ($data['surname'] ?? ''));
        $email    = trim((string) ($data['email'] ?? ''));
        $phone    = trim((string) ($data['phone'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($name === '' || $surname === '' || $email === '') {
            throw new InvalidArgumentException('name, surname and email are required to create a user.');
        }

        if ($password === '') {
            $password = 'Welcome123!';
        }

        $existing = $this->conn->query(
            "SELECT user_id FROM users WHERE email = '" . $this->conn->real_escape_string($email) . "' LIMIT 1"
        );
        if ($existing && $existing->fetch_assoc()) {
            throw new InvalidArgumentException('A user with this email already exists.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            'INSERT INTO users (name, surname, email, phone, password_hash, status) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $status = 'active';
        $stmt->bind_param('ssssss', $name, $surname, $email, $phone, $hash, $status);
        $stmt->execute();
        $userId = (int) $this->conn->insert_id;
        $stmt->close();

        return $userId;
    }

    private function normalizeLimit(int $limit): int
    {
        return max(1, min(5000, $limit));
    }

    private function normalizeOffset(int $offset): int
    {
        return max(0, $offset);
    }

    private function count(string $sql): int
    {
        $result = $this->conn->query($sql);
        $row    = $result->fetch_assoc();

        return (int) ($row['c'] ?? 0);
    }

    private function fetchAll(string $sql): array
    {
        $result = $this->conn->query($sql);
        $rows   = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }
}