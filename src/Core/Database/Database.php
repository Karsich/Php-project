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

            error_log("Database::__construct - Connecting to database");
            error_log("Connection params: host=$host, port=$port, dbname=$dbname, user=$username");

            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            
            $this->connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true
                ]
            );

            // Проверяем подключение
            $this->connection->query("SELECT 1");
            error_log("Database connection successful");

            // Проверяем существование таблицы post_reactions
            $result = $this->connection->query("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'post_reactions'
                )
            ")->fetch(PDO::FETCH_COLUMN);
            
            error_log("post_reactions table exists: " . ($result ? 'true' : 'false'));

            if ($result) {
                // Проверяем структуру таблицы
                $columns = $this->connection->query("
                    SELECT column_name, data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'post_reactions'
                ")->fetchAll();
                error_log("post_reactions table structure: " . json_encode($columns));
            }

        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
        try {
            error_log("Database::query - Starting query execution");
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                $error = $this->connection->errorInfo();
                error_log("Prepare error: " . json_encode($error));
                return [];
            }
            
            $result = $stmt->execute($params);
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Execute error: " . json_encode($error));
                return [];
            }
            
            $data = $stmt->fetchAll();
            error_log("Query returned " . count($data) . " rows");
            error_log("First row: " . json_encode($data[0] ?? null));
            
            return $data;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function execute(string $sql, array $params = []): bool {
        try {
            error_log("Database::execute - Starting query execution");
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
            error_log("In transaction: " . ($this->connection->inTransaction() ? 'true' : 'false'));
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                $error = $this->connection->errorInfo();
                error_log("Prepare error: " . json_encode($error));
                return false;
            }
            
            $result = $stmt->execute($params);
            error_log("Execute result: " . ($result ? 'true' : 'false'));
            
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Execute error: " . json_encode($error));
                if ($this->connection->inTransaction()) {
                    error_log("Rolling back transaction due to error");
                    $this->connection->rollBack();
                }
                return false;
            }
            
            // Проверяем количество затронутых строк
            $rowCount = $stmt->rowCount();
            error_log("Affected rows: " . $rowCount);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Database execute error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            if ($this->connection->inTransaction()) {
                error_log("Rolling back transaction due to exception");
                $this->connection->rollBack();
            }
            throw $e;
        }
    }

    public function lastInsertId(string $sequence = null): string {
        return $this->connection->lastInsertId($sequence);
    }
} 