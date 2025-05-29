<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance();

try {
    $result = $db->query("SELECT COUNT(*) as count FROM post_reactions");
    echo "Всего реакций в базе: " . $result[0]['count'] . "\n";

    $details = $db->query("
        SELECT 
            pr.id,
            pr.post_id,
            pr.user_id,
            p.content as post_content,
            u.username as user_username
        FROM post_reactions pr
        JOIN posts p ON pr.post_id = p.id
        JOIN users_view u ON pr.user_id = u.id
        LIMIT 5
    ");

    echo "\nПоследние 5 реакций:\n";
    foreach ($details as $row) {
        echo "ID: {$row['id']}, Post ID: {$row['post_id']}, User: {$row['user_username']}\n";
        echo "Post content: " . substr($row['post_content'], 0, 50) . "...\n\n";
    }
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
} 