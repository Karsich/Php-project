<?php

namespace App\Core\Models;

use App\Db\Services\DatabaseService;
use PDO;

class User {
    private PDO $db;
    
    public function __construct() {
        $this->db = DatabaseService::getInstance();
    }
    
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password)
            VALUES (:username, :email, :password)
            RETURNING id
        ");
        
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password']
        ]);
        
        return (int) $stmt->fetchColumn();
    }
    
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
} 