<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();

try {
    // Проверяем пользователя admin
    $admin = $db->query("SELECT * FROM users WHERE email = 'admin@example.com'");
    echo "Пользователь admin:\n";
    var_dump($admin);

    // Проверяем роль admin
    $role = $db->query("SELECT * FROM roles WHERE name = 'admin'");
    echo "\nРоль admin:\n";
    var_dump($role);

    // Проверяем связь пользователя с ролью
    if (!empty($admin) && !empty($role)) {
        $userRole = $db->query("
            SELECT * FROM user_roles 
            WHERE user_id = ? AND role_id = ?
        ", [$admin[0]['id'], $role[0]['id']]);
        echo "\nСвязь пользователя с ролью:\n";
        var_dump($userRole);
    }

} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
} 