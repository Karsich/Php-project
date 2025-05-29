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
        
        if (!isset($_SESSION['user'])) {
            error_log("User not authenticated");
            echo json_encode([
                'success' => false,
                'error' => 'Для добавления реакции необходимо войти в систему'
            ]);
            exit;
        }

        error_log("Calling PostService toggleReaction");
        $result = $this->postService->toggleReaction($id, $_SESSION['user']['id']);
        error_log("PostService result: " . json_encode($result));
        
        echo json_encode($result);
        exit;
    }
} 