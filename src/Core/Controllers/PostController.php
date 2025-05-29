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
        $authorId = $_SESSION['user']['id'];

        if (empty($content) || empty($topicId)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Заполните все поля'
            ];
            header('Location: /topics/' . $topicId);
            exit;
        }

        $result = $this->postService->createPost($authorId, $topicId, $content);
        
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
} 