<?php

namespace App\Core\Services;

use App\Core\Database\Database;

class AuthorizationService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function isAdmin($userId): bool
    {
        if (!$userId) {
            return false;
        }

        $result = $this->db->query("
            SELECT EXISTS (
                SELECT 1 
                FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE ur.user_id = ? AND r.name = 'admin'
            ) as is_admin
        ", [$userId]);

        return (bool)($result[0]['is_admin'] ?? false);
    }

    public function canManagePost($userId, $postId): bool
    {
        if (!$userId) {
            return false;
        }

        // Проверяем, является ли пользователь админом
        if ($this->isAdmin($userId)) {
            return true;
        }

        // Проверяем, является ли пользователь автором поста
        $result = $this->db->query("
            SELECT EXISTS (
                SELECT 1 
                FROM posts 
                WHERE id = ? AND author_id = ?
            ) as is_author
        ", [$postId, $userId]);

        return (bool)($result[0]['is_author'] ?? false);
    }

    public function canManageTopic($userId, $topicId): bool
    {
        if (!$userId) {
            return false;
        }

        // Проверяем, является ли пользователь админом
        if ($this->isAdmin($userId)) {
            return true;
        }

        // Проверяем, является ли пользователь автором темы
        $result = $this->db->query("
            SELECT EXISTS (
                SELECT 1 
                FROM topics 
                WHERE id = ? AND author_id = ?
            ) as is_author
        ", [$topicId, $userId]);

        return (bool)($result[0]['is_author'] ?? false);
    }
} 