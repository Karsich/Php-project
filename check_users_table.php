<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();

try {
    // Проверяем структуру таблицы users
    $tableInfo = $db->query("
        SELECT column_name, data_type, character_maximum_length
        FROM information_schema.columns
        WHERE table_name = 'users'
    ");

    echo "Структура таблицы users:\n";
    foreach ($tableInfo as $column) {
        echo json_encode($column) . "\n";
    }

    // Проверяем существующих пользователей
    $users = $db->query("SELECT id, username, email FROM users");
    echo "\nСписок пользователей:\n";
    foreach ($users as $user) {
        echo json_encode($user) . "\n";
    }

} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
} 