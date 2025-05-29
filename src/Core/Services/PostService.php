<?php

namespace App\Core\Services;

use App\Core\Database\Database;

class PostService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getPosts($topicId, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;
        
        $posts = $this->db->query("
            SELECT p.*, u.username 
            FROM posts p 
            JOIN users u ON p.author_id = u.id 
            WHERE p.topic_id = ? 
            ORDER BY p.created_at ASC 
            LIMIT ? OFFSET ?
        ", [$topicId, $limit, $offset]);

        return [
            'success' => true,
            'data' => $posts
        ];
    }

    public function createPost($userId, $topicId, $content)
    {
        // Проверяем, не закрыта ли тема
        $topic = $this->db->query("SELECT is_closed FROM topics WHERE id = ?", [$topicId]);
        if ($topic[0]['is_closed']) {
            return [
                'success' => false,
                'error' => 'Тема закрыта для новых ответов'
            ];
        }

        $this->db->query(
            "INSERT INTO posts (content, author_id, topic_id) VALUES (?, ?, ?)",
            [$content, $userId, $topicId]
        );

        return [
            'success' => true,
            'message' => 'Ответ успешно добавлен'
        ];
    }
} 