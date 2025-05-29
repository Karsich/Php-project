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

    // Добавляем роль администратора
    $db->query("
        INSERT INTO roles (name) 
        VALUES ('admin') 
        ON CONFLICT (name) DO NOTHING
    ");

    // Создаем пользователя admin с паролем admin
    $db->query("
        INSERT INTO users (username, email, password) 
        VALUES ('admin', 'admin@example.com', ?) 
        ON CONFLICT (username) DO NOTHING
    ", [password_hash('admin', PASSWORD_DEFAULT)]);

    // Назначаем роль администратора пользователю admin
    $db->query("
        INSERT INTO user_roles (user_id, role_id)
        SELECT u.id, r.id
        FROM users u, roles r
        WHERE u.username = 'admin' AND r.name = 'admin'
        ON CONFLICT (user_id, role_id) DO NOTHING
    ");

    echo "Миграция успешно выполнена!\n";
    echo "Создан пользователь admin с паролем admin\n";
} catch (Exception $e) {
    echo "Ошибка при выполнении миграции: " . $e->getMessage() . "\n";
    exit(1);
} 