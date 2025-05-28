<?php

namespace App\Auth\Services;

use App\Core\Models\User;
use Firebase\JWT\JWT;

class AuthService {
    private User $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function register(array $data): ?array {
        if ($this->userModel->findByEmail($data['email'])) {
            return null;
        }
        
        if ($this->userModel->findByUsername($data['username'])) {
            return null;
        }
        
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $id = $this->userModel->create($data);
        $user = $this->userModel->find($id);
        
        unset($user['password']);
        return $user;
    }
    
    public function login(string $username, string $password): ?array {
        $user = $this->userModel->findByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }
        
        $token = $this->generateToken($user);
        unset($user['password']);
        
        return [
            'user' => $user,
            'token' => $token
        ];
    }
    
    private function generateToken(array $user): string {
        return JWT::encode([
            'id' => $user['id'],
            'username' => $user['username'],
            'exp' => time() + (60 * 60 * 24) // 24 часа
        ], $_ENV['JWT_SECRET'], 'HS256');
    }
    
    public function validateToken(string $token): ?array {
        try {
            $decoded = JWT::decode($token, $_ENV['JWT_SECRET'], ['HS256']);
            return $this->userModel->find($decoded->id);
        } catch (\Exception $e) {
            return null;
        }
    }
} 