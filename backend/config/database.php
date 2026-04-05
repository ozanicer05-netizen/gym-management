<?php

declare(strict_types=1);

final class Database
{
    private const HOST = 'localhost';
    private const USERNAME = 'root';
    private const PASSWORD = '';
    private const DATABASE = 'gym_db';

    public static function connect(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $conn = new mysqli(
            self::HOST,
            self::USERNAME,
            self::PASSWORD,
            self::DATABASE
        );

        $conn->set_charset('utf8mb4');

        return $conn;
    }
}
