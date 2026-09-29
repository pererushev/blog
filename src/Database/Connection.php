<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;

    /**
     * Singleton: одно соединение на весь запрос.
     * Приватный конструктор запрещает new Connection().
     */
    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $db   = $_ENV['DB_DATABASE'];
            $user = $_ENV['DB_USERNAME'];
            $pass = $_ENV['DB_PASSWORD'];

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES    => false, // настоящие prepared statements
                ]);
            } catch (PDOException $e) {
                // В продакшене — логируем, не показываем детали
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /** Запрет клонирования */
    private function __clone() {}
}