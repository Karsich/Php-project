<?php

namespace App\Core\Controllers;

use App\Core\Services\PostService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Core\Database\Database;

class PostController
{
    private $postService;
    private $db;

    public function __construct(PostService $postService = null)
    {
        $this->db = Database::getInstance();
        $this->postService = $postService ?? new PostService();
    }

    public function getPosts(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $topicId = $params['topic_id'] ?? null;
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        
        $result = $this->postService->getPosts($topicId, $page, $limit);
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createPost(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $userId = $request->getAttribute('user_id');
        
        $result = $this->postService->createPost(
            $userId,
            $data['topic_id'],
            $data['content']
        );
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json')
                       ->withStatus(201);
    }

    public function create()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для создания ответа необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        $content = $_POST['content'] ?? '';
        $topicId = $_POST['topic_id'] ?? '';
        $replyToId = !empty($_POST['reply_to_id']) ? (int)$_POST['reply_to_id'] : null;
        $authorId = $_SESSION['user']['id'];

        if (empty($content) || empty($topicId)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Заполните все поля'
            ];
            header('Location: /topics/' . $topicId);
            exit;
        }

        $result = $this->postService->createPost($authorId, $topicId, $content, $replyToId);
        
        if (!$result['success']) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => $result['error']
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
        }
        
        header('Location: /topics/' . $topicId);
        exit;
    }

    public function showEditForm($id)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для редактирования ответа необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        $result = $this->postService->getPost($id, $_SESSION['user']['id']);
        
        if (!$result['success']) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => $result['error']
            ];
            header('Location: /topics/' . $result['data']['topic_id']);
            exit;
        }

        include __DIR__ . '/../Views/posts/edit.php';
    }

    public function update($id)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для редактирования ответа необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        $content = $_POST['content'] ?? '';
        
        if (empty($content)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Содержание ответа не может быть пустым'
            ];
            header('Location: /posts/' . $id . '/edit');
            exit;
        }

        $result = $this->postService->updatePost($id, $_SESSION['user']['id'], $content);
        
        if (!$result['success']) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => $result['error']
            ];
            header('Location: /posts/' . $id . '/edit');
        } else {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            $post = $this->postService->getPost($id, $_SESSION['user']['id']);
            header('Location: /topics/' . $post['data']['topic_id']);
        }
        exit;
    }

    public function delete($id)
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Для удаления ответа необходимо войти в систему'
            ];
            header('Location: /auth/login');
            exit;
        }

        $post = $this->postService->getPost($id, $_SESSION['user']['id']);
        $topicId = $post['data']['topic_id'];
        
        $result = $this->postService->deletePost($id, $_SESSION['user']['id']);
        
        if (!$result['success']) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => $result['error']
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
        }
        
        header('Location: /topics/' . $topicId);
        exit;
    }

    public function toggleReaction($id)
    {
        error_log("toggleReaction controller called with id: $id");
        error_log("Session data: " . json_encode($_SESSION));
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Content type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
        
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        
        // Если это GET-запрос, возвращаем текущее состояние реакции
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                // Проверяем существование поста
                $post = $this->db->query("
                    SELECT p.*, t.is_closed 
                    FROM posts p 
                    JOIN topics t ON p.topic_id = t.id 
                    WHERE p.id = ? AND NOT p.is_deleted
                ", [$id]);

                if (empty($post)) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Пост не найден или был удален'
                    ]);
                    exit;
                }

                // Получаем количество реакций и статус для текущего пользователя
                $result = $this->db->query("
                    WITH reaction_data AS (
                        SELECT COUNT(*) as total_count
                        FROM post_reactions
                        WHERE post_id = ?
                    )
                    SELECT 
                        rd.total_count as reaction_count,
                        EXISTS (
                            SELECT 1 
                            FROM post_reactions 
                            WHERE post_id = ? AND user_id = ?
                        ) as has_user_reaction
                    FROM reaction_data rd
                ", [$id, $id, $userId]);

                echo json_encode([
                    'success' => true,
                    'reaction_count' => (int)$result[0]['reaction_count'],
                    'has_user_reaction' => (bool)$result[0]['has_user_reaction']
                ]);
                exit;
            } catch (\Exception $e) {
                error_log("Error getting reaction status: " . $e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'Ошибка при получении статуса реакции'
                ]);
                exit;
            }
        }

        // Для POST-запросов проверяем авторизацию
        if (!isset($_SESSION['user'])) {
            error_log("User not authenticated");
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Для добавления реакции необходимо войти в систему'
            ]);
            exit;
        }

        error_log("Authenticated user ID: " . $_SESSION['user']['id']);
        error_log("Calling PostService toggleReaction");
        $result = $this->postService->toggleReaction($id, $_SESSION['user']['id']);
        error_log("PostService result: " . json_encode($result));
        
        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
        exit;
    }
} 