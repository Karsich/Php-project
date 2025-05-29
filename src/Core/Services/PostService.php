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
            SELECT 
                p.*, 
                u.username,
                COALESCE((SELECT COUNT(*) FROM post_reactions pr WHERE pr.post_id = p.id), 0) as reaction_count,
                CASE 
                    WHEN p.reply_to_id IS NOT NULL THEN (
                        SELECT JSON_BUILD_OBJECT(
                            'content', p2.content,
                            'username', u2.username
                        )
                        FROM posts p2 
                        JOIN users u2 ON p2.author_id = u2.id 
                        WHERE p2.id = p.reply_to_id AND NOT p2.is_deleted
                    )
                    ELSE NULL
                END as reply_data
            FROM posts p 
            JOIN users u ON p.author_id = u.id 
            WHERE p.topic_id = ? 
            ORDER BY p.created_at ASC 
            LIMIT ? OFFSET ?
        ", [$topicId, $limit, $offset]);

        // Обработка данных ответа
        foreach ($posts as &$post) {
            // Инициализация значений по умолчанию
            if (!isset($post['reaction_count'])) {
                $post['reaction_count'] = 0;
            }
            $post['has_user_reaction'] = false;

            if ($post['reply_to_id']) {
                if (!empty($post['reply_data'])) {
                    $replyData = json_decode($post['reply_data'], true);
                    $post['reply_to_username'] = $replyData['username'];
                    $post['reply_to_content'] = $replyData['content'];
                } else {
                    $post['reply_to_username'] = '';
                    $post['reply_to_content'] = '';
                }
            }
            unset($post['reply_data']);
        }

        // Получаем реакции текущего пользователя
        if (isset($_SESSION['user']) && !empty($posts)) {
            $postIds = array_column($posts, 'id');
            $placeholders = str_repeat('?,', count($postIds) - 1) . '?';
            $userReactions = $this->db->query(
                "SELECT post_id FROM post_reactions WHERE user_id = ? AND post_id IN ($placeholders)",
                array_merge([$_SESSION['user']['id']], $postIds)
            );

            $userReactedPosts = array_column($userReactions, 'post_id');
            foreach ($posts as &$post) {
                $post['has_user_reaction'] = in_array($post['id'], $userReactedPosts);
            }
        }

        return [
            'success' => true,
            'data' => $posts
        ];
    }

    public function getPost($postId, $userId)
    {
        $post = $this->db->query("
            SELECT p.*, t.is_closed, t.author_id as topic_author_id
            FROM posts p
            JOIN topics t ON p.topic_id = t.id
            WHERE p.id = ?
        ", [$postId]);

        if (empty($post)) {
            return [
                'success' => false,
                'error' => 'Ответ не найден'
            ];
        }

        // Проверяем права на редактирование
        if ($post[0]['author_id'] !== $userId) {
            return [
                'success' => false,
                'error' => 'У вас нет прав для редактирования этого ответа'
            ];
        }

        return [
            'success' => true,
            'data' => $post[0]
        ];
    }

    public function createPost($userId, $topicId, $content, $replyToId = null)
    {
        // Проверяем, не закрыта ли тема
        $topic = $this->db->query("SELECT is_closed FROM topics WHERE id = ?", [$topicId]);
        if ($topic[0]['is_closed']) {
            return [
                'success' => false,
                'error' => 'Тема закрыта для новых ответов'
            ];
        }

        // Если это ответ на другой пост, проверяем его существование
        if (!empty($replyToId)) {
            $replyTo = $this->db->query("
                SELECT id FROM posts 
                WHERE id = ? AND topic_id = ? AND NOT is_deleted
            ", [$replyToId, $topicId]);

            if (empty($replyTo)) {
                return [
                    'success' => false,
                    'error' => 'Ответ, на который вы пытаетесь ответить, не существует или был удален'
                ];
            }
        }

        $this->db->query(
            "INSERT INTO posts (content, author_id, topic_id, reply_to_id) VALUES (?, ?, ?, ?)",
            [$content, $userId, $topicId, empty($replyToId) ? null : $replyToId]
        );

        return [
            'success' => true,
            'message' => 'Ответ успешно добавлен'
        ];
    }

    public function updatePost($postId, $userId, $content)
    {
        $post = $this->getPost($postId, $userId);
        
        if (!$post['success']) {
            return $post;
        }

        if ($post['data']['is_closed']) {
            return [
                'success' => false,
                'error' => 'Нельзя редактировать ответы в закрытой теме'
            ];
        }

        $this->db->query(
            "UPDATE posts SET content = ?, updated_at = NOW() WHERE id = ?",
            [$content, $postId]
        );

        return [
            'success' => true,
            'message' => 'Ответ успешно обновлен'
        ];
    }

    public function deletePost($postId, $userId)
    {
        $post = $this->getPost($postId, $userId);
        
        if (!$post['success']) {
            return $post;
        }

        if ($post['data']['is_closed'] && $userId !== $post['data']['topic_author_id']) {
            return [
                'success' => false,
                'error' => 'Нельзя удалять ответы в закрытой теме'
            ];
        }

        // Используем мягкое удаление
        $this->db->query(
            "UPDATE posts SET is_deleted = TRUE, updated_at = NOW() WHERE id = ?",
            [$postId]
        );

        return [
            'success' => true,
            'message' => 'Ответ успешно удален'
        ];
    }

    public function toggleReaction($postId, $userId)
    {
        // Проверяем существование поста
        $post = $this->db->query("
            SELECT p.*, t.is_closed 
            FROM posts p 
            JOIN topics t ON p.topic_id = t.id 
            WHERE p.id = ? AND NOT p.is_deleted
        ", [$postId]);

        if (empty($post)) {
            return [
                'success' => false,
                'error' => 'Пост не найден или был удален'
            ];
        }

        // Нельзя ставить реакции на свои посты
        if ($post[0]['author_id'] === $userId) {
            return [
                'success' => false,
                'error' => 'Нельзя ставить реакции на свои ответы'
            ];
        }

        // Проверяем, есть ли уже реакция
        $reaction = $this->db->query(
            "SELECT id FROM post_reactions WHERE post_id = ? AND user_id = ?",
            [$postId, $userId]
        );

        if (empty($reaction)) {
            // Добавляем реакцию
            $this->db->query(
                "INSERT INTO post_reactions (post_id, user_id) VALUES (?, ?)",
                [$postId, $userId]
            );
            $message = 'Реакция добавлена';
        } else {
            // Удаляем реакцию
            $this->db->query(
                "DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?",
                [$postId, $userId]
            );
            $message = 'Реакция удалена';
        }

        // Получаем обновленное количество реакций
        $reactionCount = $this->db->query(
            "SELECT COUNT(*) as count FROM post_reactions WHERE post_id = ?",
            [$postId]
        );

        return [
            'success' => true,
            'message' => $message,
            'reaction_count' => $reactionCount[0]['count']
        ];
    }
} 