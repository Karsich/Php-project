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
                CASE WHEN EXISTS (SELECT 1 FROM post_reactions pr WHERE pr.post_id = p.id AND pr.user_id = ?) THEN true ELSE false END as has_user_reaction,
                CASE 
                    WHEN p.reply_to_id IS NOT NULL THEN (
                        SELECT row_to_json(r) 
                        FROM (
                            SELECT 
                                CASE WHEN p2.is_deleted THEN NULL ELSE p2.content END as content,
                                u2.username as username,
                                p2.id as reply_to_id
                            FROM posts p2 
                            JOIN users_view u2 ON p2.author_id = u2.id 
                            WHERE p2.id = p.reply_to_id
                        ) r
                    )
                    ELSE NULL
                END as reply_data
            FROM posts p 
            JOIN users_view u ON p.author_id = u.id 
            WHERE p.topic_id = ? AND NOT p.is_deleted
            ORDER BY p.created_at ASC 
            LIMIT ? OFFSET ?
        ", [isset($_SESSION['user']) ? $_SESSION['user']['id'] : null, $topicId, $limit, $offset]);

        // Обработка данных ответа
        foreach ($posts as &$post) {
            if (!empty($post['reply_data'])) {
                $replyData = json_decode($post['reply_data'], true);
                
                $post['reply_to_id'] = $replyData['reply_to_id'] ?? null;
                $post['reply_to_content'] = $replyData['content'] ?? null;
                $post['reply_to_username'] = $replyData['username'] ?? null;
            }
        }
        
        return $posts;
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
                WHERE id = ? AND topic_id = ?
            ", [$replyToId, $topicId]);

            if (empty($replyTo)) {
                return [
                    'success' => false,
                    'error' => 'Ответ, на который вы пытаетесь ответить, не существует'
                ];
            }
        }

        $this->db->query(
            "INSERT INTO posts (content, author_id, topic_id, reply_to_id) VALUES (?, ?, ?, ?)",
            [$content, $userId, $topicId, $replyToId]
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
        error_log("toggleReaction called with postId: $postId, userId: $userId");
        
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Проверяем существование поста
            $post = $this->db->query("
                SELECT p.*, t.is_closed 
                FROM posts p 
                JOIN topics t ON p.topic_id = t.id 
                WHERE p.id = ? AND NOT p.is_deleted
                FOR UPDATE
            ", [$postId]);

            if (empty($post)) {
                $this->db->getConnection()->rollBack();
                error_log("Post not found or deleted");
                return [
                    'success' => false,
                    'error' => 'Пост не найден или был удален'
                ];
            }

            // Нельзя ставить реакции на свои посты
            if ((int)$post[0]['author_id'] === (int)$userId) {
                $this->db->getConnection()->rollBack();
                error_log("User trying to react to their own post");
                return [
                    'success' => false,
                    'error' => 'Нельзя ставить реакции на свои ответы'
                ];
            }

            // Проверяем, есть ли уже реакция
            $reaction = $this->db->query("
                SELECT id FROM post_reactions 
                WHERE post_id = ? AND user_id = ?
                FOR UPDATE
            ", [$postId, $userId]);

            error_log("Existing reaction check result: " . json_encode($reaction));

            if (empty($reaction)) {
                // Создаем реакцию
                error_log("Attempting to create reaction");
                $result = $this->db->execute("
                    INSERT INTO post_reactions (post_id, user_id) 
                    VALUES (?, ?)
                ", [$postId, $userId]);
                error_log("Insert result: " . ($result ? 'true' : 'false'));
            } else {
                // Удаляем реакцию
                error_log("Attempting to delete reaction");
                $result = $this->db->execute("
                    DELETE FROM post_reactions 
                    WHERE post_id = ? AND user_id = ?
                ", [$postId, $userId]);
                error_log("Delete result: " . ($result ? 'true' : 'false'));
            }

            // Получаем обновленное количество реакций
            $reactionCount = $this->db->query("
                SELECT COUNT(*) as count 
                FROM post_reactions 
                WHERE post_id = ?
            ", [$postId])[0]['count'];

            error_log("Updated reaction count: $reactionCount");

            $this->db->getConnection()->commit();
            
            return [
                'success' => true,
                'reaction_count' => (int)$reactionCount,
                'message' => empty($reaction) ? 'Реакция добавлена' : 'Реакция удалена'
            ];
        } catch (\Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log("Error in toggleReaction: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Ошибка при обработке реакции: ' . $e->getMessage()
            ];
        }
    }
} 