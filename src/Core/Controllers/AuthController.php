<?php

namespace App\Core\Controllers;

use App\Core\Database\Database;

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function showLoginForm()
    {
        include __DIR__ . '/../Views/auth/login.php';
    }

    public function showRegistrationForm()
    {
        include __DIR__ . '/../Views/auth/register.php';
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->db->query(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );

        if (!empty($user) && password_verify($password, $user[0]['password'])) {
            $_SESSION['user'] = $user[0];
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Добро пожаловать!'
            ];
            header('Location: /');
            exit;
        }

        $_SESSION['flash'] = [
            'type' => 'danger',
            'message' => 'Неверный email или пароль'
        ];
        header('Location: /auth/login');
        exit;
    }

    public function register()
    {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($password !== $passwordConfirm) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Пароли не совпадают'
            ];
            header('Location: /auth/register');
            exit;
        }

        $existingUser = $this->db->query(
            "SELECT * FROM users WHERE email = ? OR username = ?",
            [$email, $username]
        );

        if (!empty($existingUser)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Пользователь с таким email или именем пользователя уже существует'
            ];
            header('Location: /auth/register');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->db->query(
            "INSERT INTO users (username, email, password) VALUES (?, ?, ?)",
            [$username, $email, $hashedPassword]
        );

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Регистрация успешна! Теперь вы можете войти.'
        ];
        header('Location: /auth/login');
        exit;
    }

    public function logout()
    {
        session_destroy();
        header('Location: /');
        exit;
    }
} 