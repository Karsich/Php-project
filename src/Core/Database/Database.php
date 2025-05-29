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
            $dbname = $_ENV['DB_NAME'] ?? 'forum_db';
            $username = $_ENV['DB_USER'] ?? 'postgres';
            $password = $_ENV['DB_PASSWORD'] ?? '';

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
        } catch (PDOException $e) {
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
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId(string $sequence = null): string {
        return $this->connection->lastInsertId($sequence);
    }
} 