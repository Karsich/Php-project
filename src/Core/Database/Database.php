<?php

namespace App\Core\Database;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '5432';
            $dbname = $_ENV['DB_NAME'] ?? 'forum';
            $username = $_ENV['DB_USER'] ?? 'postgres';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            error_log("Connecting to database: host=$host, port=$port, dbname=$dbname, user=$username");

            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            
            $this->connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            // Проверяем подключение
            $this->connection->query("SELECT 1");
            error_log("Database connection successful");
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new PDOException($e->getMessage());
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function query(string $sql, array $params = []): array {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool {
        try {
            error_log("Executing SQL: " . $sql);
            error_log("With params: " . json_encode($params));
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            error_log("Execute result: " . ($result ? 'true' : 'false'));
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Execute error: " . json_encode($error));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            throw $e;
        }
    }

    public function lastInsertId(string $sequence = null): string {
        return $this->connection->lastInsertId($sequence);
    }
} 