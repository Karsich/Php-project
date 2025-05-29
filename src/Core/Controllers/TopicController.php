<?php

namespace App\Core\Controllers;

use App\Core\Database\Database;

class TopicController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
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
            JOIN users u ON t.author_id = u.id 
            WHERE t.id = ?
        ", [$id]);

        if (empty($topic)) {
            http_response_code(404);
            include __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $posts = $this->db->query("
            SELECT p.*, u.username 
            FROM posts p 
            JOIN users u ON p.author_id = u.id 
            WHERE p.topic_id = ? 
            ORDER BY p.created_at ASC
        ", [$id]);

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

        $topic = $this->db->query("
            SELECT * FROM topics WHERE id = ? AND author_id = ?
        ", [$id, $_SESSION['user']['id']]);

        if (empty($topic)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Тема не найдена или у вас нет прав для её редактирования'
            ];
            header('Location: /topics/' . $id);
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

        $topic = $this->db->query("
            SELECT * FROM topics WHERE id = ? AND author_id = ?
        ", [$id, $_SESSION['user']['id']]);

        if (empty($topic)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Тема не найдена или у вас нет прав для её редактирования'
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

        $topic = $this->db->query("
            SELECT * FROM topics WHERE id = ? AND author_id = ?
        ", [$id, $_SESSION['user']['id']]);

        if (empty($topic)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Тема не найдена или у вас нет прав для её управления'
            ];
            header('Location: /topics/' . $id);
            exit;
        }

        $newStatus = !$topic[0]['is_closed'];
        $this->db->query(
            "UPDATE topics SET is_closed = ? WHERE id = ?",
            [$newStatus, $id]
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

        $topic = $this->db->query("
            SELECT * FROM topics WHERE id = ? AND author_id = ?
        ", [$id, $_SESSION['user']['id']]);

        if (empty($topic)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Тема не найдена или у вас нет прав для её удаления'
            ];
            header('Location: /topics/' . $id);
            exit;
        }

        $this->db->query("DELETE FROM topics WHERE id = ?", [$id]);

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Тема успешно удалена'
        ];
        header('Location: /');
        exit;
    }
} 