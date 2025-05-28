<?php

namespace App\Auth\Controllers;

use App\Auth\Services\AuthService;

class AuthController {
    private AuthService $authService;
    
    public function __construct() {
        $this->authService = new AuthService();
    }
    
    public function register(): void {
        $data = $this->getRequestBody();
        if (!$data) {
            $this->error('Invalid request data');
            return;
        }
        
        $required = ['username', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->error("Field {$field} is required");
                return;
            }
        }
        
        $user = $this->authService->register($data);
        if (!$user) {
            $this->error('Registration failed');
            return;
        }
        
        $this->success($user, 'User registered successfully');
    }
    
    public function login(): void {
        $data = $this->getRequestBody();
        if (!$data) {
            $this->error('Invalid request data');
            return;
        }
        
        $required = ['username', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->error("Field {$field} is required");
                return;
            }
        }
        
        $result = $this->authService->login($data['username'], $data['password']);
        if (!$result) {
            $this->error('Invalid credentials', 401);
            return;
        }
        
        $this->success($result, 'Login successful');
    }
    
    private function getRequestBody(): ?array {
        return json_decode(file_get_contents('php://input'), true);
    }
    
    private function success($data = null, string $message = ''): void {
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }
    
    private function error(string $message, int $code = 400): void {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
} 