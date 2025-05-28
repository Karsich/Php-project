<?php

namespace App\Db\Services;

use PDO;
use PDOException;

class DatabaseService {
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf("pgsql:host=%s;dbname=%s;user=%s;password=%s",
                    $_ENV['DB_HOST'],
                    $_ENV['DB_NAME'],
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS']
                );
                
                self::$instance = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
    
    public static function beginTransaction(): bool {
        return self::getInstance()->beginTransaction();
    }
    
    public static function commit(): bool {
        return self::getInstance()->commit();
    }
    
    public static function rollback(): bool {
        return self::getInstance()->rollBack();
    }
} 