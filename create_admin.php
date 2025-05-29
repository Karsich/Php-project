<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();

try {
    // Создаем таблицу ролей, если её нет
    $db->query("
        CREATE TABLE IF NOT EXISTS roles (
            id SERIAL PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            parent_id INTEGER REFERENCES roles(id)
        )
    ");

    // Создаем таблицу связей пользователей и ролей, если её нет
    $db->query("
        CREATE TABLE IF NOT EXISTS user_roles (
            user_id INTEGER REFERENCES users(id),
            role_id INTEGER REFERENCES roles(id),
            PRIMARY KEY (user_id, role_id)
        )
    ");

    // Удаляем старого пользователя admin, если он есть
    $db->query("DELETE FROM user_roles WHERE user_id IN (SELECT id FROM users WHERE username = 'admin')");
    $db->query("DELETE FROM users WHERE username = 'admin'");

    // Добавляем роль администратора
    $db->query("
        INSERT INTO roles (name) 
        VALUES ('admin') 
        ON CONFLICT (name) DO NOTHING
        RETURNING id
    ");

    // Создаем пользователя admin с паролем admin
    $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
    echo "Создаем пользователя admin с хешем пароля: " . $hashedPassword . "\n";
    
    $db->query("
        INSERT INTO users (username, email, password) 
        VALUES ('admin', 'admin@example.com', ?) 
        RETURNING id
    ", [$hashedPassword]);

    // Получаем ID пользователя и роли
    $adminUser = $db->query("SELECT id FROM users WHERE username = 'admin'")[0];
    $adminRole = $db->query("SELECT id FROM roles WHERE name = 'admin'")[0];

    // Назначаем роль администратора пользователю admin
    $db->query("
        INSERT INTO user_roles (user_id, role_id)
        VALUES (?, ?)
    ", [$adminUser['id'], $adminRole['id']]);

    echo "Администратор успешно создан!\n";
    echo "Логин: admin@example.com\n";
    echo "Пароль: admin\n";
} catch (Exception $e) {
    echo "Ошибка при создании администратора: " . $e->getMessage() . "\n";
    exit(1);
} 