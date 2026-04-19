<?php<?php



declare(strict_types=1);declare(strict_types=1);



final class GymRepositoryrequire_once __DIR__ . '/../config/database.php';

{

    private mysqli $conn;final class GymRepository

{

    public function __construct()    private mysqli $conn;

    {

        $this->conn = Database::connect();    public function __construct()

    }    {

        $this->conn = Database::connect();

    public function getDashboardStats(): array    }

    {

        return [    public function getDashboardStats(): array

            'totalMembers' => $this->count("SELECT COUNT(*) AS c FROM members WHERE status='active'"),    {

            'totalTrainers' => $this->count("SELECT COUNT(*) AS c FROM trainers WHERE availability_status='active'"),        return [

            'totalClasses' => $this->count('SELECT COUNT(*) AS c FROM classes'),            'totalMembers' => $this->count("SELECT COUNT(*) AS c FROM members WHERE status='active'"),

            'totalEquipment' => $this->count("SELECT COUNT(*) AS c FROM equipment WHERE status='active'"),            'totalTrainers' => $this->count("SELECT COUNT(*) AS c FROM trainers WHERE availability_status='active'"),

            'expiringSoon' => $this->count("SELECT COUNT(*) AS c FROM subscriptions WHERE status='active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"),            'totalClasses' => $this->count('SELECT COUNT(*) AS c FROM classes'),

            'maintenanceDue' => $this->count("SELECT COUNT(*) AS c FROM equipment WHERE status='maintenance'"),            'totalEquipment' => $this->count("SELECT COUNT(*) AS c FROM equipment WHERE status='active'"),

        ];            'expiringSoon' => $this->count("SELECT COUNT(*) AS c FROM subscriptions WHERE status='active' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"),

    }            'maintenanceDue' => $this->count("SELECT COUNT(*) AS c FROM equipment WHERE status='maintenance'"),

        ];

    public function listMembers(string $search = '', string $status = '', int $limit = 50): array    }

    {

        $search = trim($search);    public function listMembers(string $search = '', string $status = '', int $limit = 50): array

        $status = trim($status);    {

        $limit = $this->normalizeLimit($limit);        $search = trim($search);

        $status = trim($status);

        $safeSearch = $this->conn->real_escape_string($search);        $limit = max(1, min(200, $limit));

        $safeStatus = $this->conn->real_escape_string($status);

        $safeSearch = $this->conn->real_escape_string($search);

        $where = 'WHERE 1=1';        $safeStatus = $this->conn->real_escape_string($status);

        if ($safeSearch !== '') {

            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";        $where = 'WHERE 1=1';

        }

        if ($safeStatus !== '') {        if ($safeSearch !== '') {

            $where .= " AND m.status = '{$safeStatus}'";            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";

        }        }



        $sql = "        if ($safeStatus !== '') {

            SELECT            $where .= " AND m.status = '{$safeStatus}'";

                m.member_id,        }

                u.name,

                u.surname,        $sql = "

                u.email,            SELECT

                u.phone,                m.member_id,

                b.branch_name,                u.name,

                m.join_date,                u.surname,

                m.status                u.email,

            FROM members m                u.phone,

            JOIN users u ON m.user_id = u.user_id                b.branch_name,

            JOIN branches b ON m.branch_id = b.branch_id                m.join_date,

            {$where}                m.status

            ORDER BY m.member_id DESC            FROM members m

            LIMIT {$limit}            JOIN users u ON m.user_id = u.user_id

        ";            JOIN branches b ON m.branch_id = b.branch_id

            {$where}

        return $this->fetchAll($sql);            ORDER BY m.join_date DESC

    }            LIMIT {$limit}

        ";

    public function listTrainers(string $search = '', string $status = '', int $limit = 50): array

    {        $result = $this->conn->query($sql);

        $search = trim($search);        $members = [];

        $status = trim($status);

        $limit = $this->normalizeLimit($limit);        while ($row = $result->fetch_assoc()) {

            $members[] = $row;

        $safeSearch = $this->conn->real_escape_string($search);        }

        $safeStatus = $this->conn->real_escape_string($status);

        return $members;

        $where = 'WHERE 1=1';    }

        if ($safeSearch !== '') {

            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";    public function listTrainers(string $search = '', string $status = '', int $limit = 50): array

        }    {

        if ($safeStatus !== '') {        $search = trim($search);

            $where .= " AND t.availability_status = '{$safeStatus}'";        $status = trim($status);

        }        $limit = max(1, min(200, $limit));



        $sql = "        $safeSearch = $this->conn->real_escape_string($search);

            SELECT        $safeStatus = $this->conn->real_escape_string($status);

                t.trainer_id,

                u.name,        $where = 'WHERE 1=1';

                u.surname,

                u.email,        if ($safeSearch !== '') {

                u.phone,            $where .= " AND (u.name LIKE '%{$safeSearch}%' OR u.surname LIKE '%{$safeSearch}%' OR u.email LIKE '%{$safeSearch}%')";

                b.branch_name,        }

                t.specialization,

                t.availability_status        if ($safeStatus !== '') {

            FROM trainers t            $where .= " AND t.availability_status = '{$safeStatus}'";

            JOIN users u ON t.user_id = u.user_id        }

            JOIN branches b ON t.branch_id = b.branch_id

            {$where}        $sql = "

            ORDER BY t.trainer_id DESC            SELECT

            LIMIT {$limit}                t.trainer_id,

        ";                u.name,

                u.surname,

        return $this->fetchAll($sql);                u.email,

    }                u.phone,

                t.availability_status

    public function listClasses(string $search = '', string $level = '', int $limit = 50): array            FROM trainers t

    {            JOIN users u ON t.user_id = u.user_id

        $search = trim($search);            {$where}

        $level = trim($level);            ORDER BY t.trainer_id DESC

        $limit = $this->normalizeLimit($limit);            LIMIT {$limit}

        ";

        $safeSearch = $this->conn->real_escape_string($search);

        $safeLevel = $this->conn->real_escape_string($level);        $result = $this->conn->query($sql);

        $trainers = [];

        $where = 'WHERE 1=1';

        if ($safeSearch !== '') {        while ($row = $result->fetch_assoc()) {

            $where .= " AND (c.class_name LIKE '%{$safeSearch}%' OR b.branch_name LIKE '%{$safeSearch}%' OR CONCAT(u.name, ' ', u.surname) LIKE '%{$safeSearch}%')";            $trainers[] = $row;

        }        }

        if ($safeLevel !== '') {

            $where .= " AND c.level = '{$safeLevel}'";        return $trainers;

        }    }



        $sql = "    private function count(string $sql): int

            SELECT    {

                c.class_id,        $result = $this->conn->query($sql);

                c.class_name,        $row = $result->fetch_assoc();

                c.capacity,

                c.duration_min,        return (int) ($row['c'] ?? 0);

                c.level,    }

                b.branch_name,}

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
