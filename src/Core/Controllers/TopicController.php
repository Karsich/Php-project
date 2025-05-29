<?php

namespace App\Core\Controllers;

use App\Core\Database\Database;
use App\Core\Services\AuthorizationService;

class TopicController
{
    private $db;
    private $authService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->authService = new AuthorizationService();
    }

    public function getTopics(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        
        $result = $this->topicService->getTopics($page, $limit);
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createTopic(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $userId = $request->getAttribute('user_id');
        
        $result = $this->topicService->createTopic(
            $userId,
            $data['title'],
            $data['description'],
            $data['category_id']
        );
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')
                       ->withStatus(201);
    }

    public function showCreateForm()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для создания темы необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        include __DIR__ . '/../Views/topics/create.php';
    }

    public function create()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для создания темы необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $authorId = $_SESSION['user']['id'];

        if (empty($title)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Название темы не может быть пустым'
            ];
            header('Location: /topics/create');
            exit;
        }

        $this->db->query(
            "INSERT INTO topics (title, description, author_id) VALUES (?, ?, ?)",
            [$title, $description, $authorId]
        );

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Тема успешно создана!'
        ];
        header('Location: /');
        exit;
    }

    public function show($id)
    {
        $topic = $this->db->query("
            SELECT t.*, u.username 
            FROM topics t 
            JOIN users_view u ON t.author_id = u.id 
            WHERE t.id = ?
        ", [$id]);

        if (empty($topic)) {
            http_response_code(404);
            include __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $currentUserId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : 0;
        
        $posts = $this->db->query("
            WITH reaction_counts AS (
                SELECT 
                    post_id,
                    COUNT(*) as count
                FROM post_reactions
                GROUP BY post_id
            )
            SELECT 
                p.*,
                u.username,
                COALESCE(rc.count, 0) as reaction_count,
                EXISTS (
                    SELECT 1 
                    FROM post_reactions pr 
                    WHERE pr.post_id = p.id 
                    AND pr.user_id = ?
                ) as has_user_reaction,
                rp.content as reply_to_content,
                ru.username as reply_to_username
            FROM posts p 
            JOIN users_view u ON p.author_id = u.id 
            LEFT JOIN reaction_counts rc ON rc.post_id = p.id
            LEFT JOIN posts rp ON p.reply_to_id = rp.id
            LEFT JOIN users_view ru ON rp.author_id = ru.id
            WHERE p.topic_id = ? 
            ORDER BY p.created_at ASC
        ", [$currentUserId, $id]);

        $authService = $this->authService;
        include __DIR__ . '/../Views/topics/show.php';
    }

    public function showEditForm($id)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для редактирования темы необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        if (!$this->authService->canManageTopic($_SESSION['user']['id'], $id)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'У вас нет прав для редактирования этой темы'
            ];
            header('Location: /topics/' . $id);
            exit;
        }

        $topic = $this->db->query("SELECT * FROM topics WHERE id = ?", [$id]);

        if (empty($topic)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Тема не найдена'
            ];
            header('Location: /');
            exit;
        }

        include __DIR__ . '/../Views/topics/edit.php';
    }

    public function update($id)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для редактирования темы необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        if (!$this->authService->canManageTopic($_SESSION['user']['id'], $id)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'У вас нет прав для редактирования этой темы'
            ];
            header('Location: /topics/' . $id);
            exit;
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($title)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Название темы не может быть пустым'
            ];
            header('Location: /topics/' . $id . '/edit');
            exit;
        }

        $this->db->query(
            "UPDATE topics SET title = ?, description = ? WHERE id = ?",
            [$title, $description, $id]
        );

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Тема успешно обновлена'
        ];
        header('Location: /topics/' . $id);
        exit;
    }

    public function toggleStatus($id)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для управления темой необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        if (!$this->authService->canManageTopic($_SESSION['user']['id'], $id)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'У вас нет прав для управления этой темой'
            ];
            header('Location: /topics/' . $id);
            exit;
        }

        $topic = $this->db->query("SELECT * FROM topics WHERE id = ?", [$id]);

        if (empty($topic)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Тема не найдена'
            ];
            header('Location: /topics/' . $id);
            exit;
        }

        $currentStatus = (bool)$topic[0]['is_closed'];
        $newStatus = !$currentStatus;
        
        $this->db->query(
            "UPDATE topics SET is_closed = ?, updated_at = NOW() WHERE id = ?",
            [$newStatus ? 't' : 'f', $id]
        );

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => $newStatus ? 'Тема закрыта' : 'Тема открыта'
        ];
        header('Location: /topics/' . $id);
        exit;
    }

    public function delete($id)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для удаления темы необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        if (!$this->authService->canManageTopic($_SESSION['user']['id'], $id)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'У вас нет прав для удаления этой темы'
            ];
            header('Location: /topics/' . $id);
            exit;
        }

        try {
            // Начинаем транзакцию
            $this->db->beginTransaction();

            // Сначала удаляем все реакции к постам этой темы
            $this->db->query("
                DELETE FROM post_reactions 
                WHERE post_id IN (SELECT id FROM posts WHERE topic_id = ?)
            ", [$id]);

            // Затем удаляем все посты темы
            $this->db->query("DELETE FROM posts WHERE topic_id = ?", [$id]);

            // И наконец удаляем саму тему
            $this->db->query("DELETE FROM topics WHERE id = ?", [$id]);

            // Подтверждаем транзакцию
            $this->db->commit();

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Тема успешно удалена'
            ];
            header('Location: /');
        } catch (\Exception $e) {
            // В случае ошибки откатываем транзакцию
            $this->db->rollBack();
            
            error_log("Ошибка при удалении темы: " . $e->getMessage());
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Произошла ошибка при удалении темы'
            ];
            header('Location: /topics/' . $id);
        }
        exit;
    }
} 