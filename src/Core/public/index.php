<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Config\Config;
use App\Core\Database\Database;
use App\Core\Controllers\AuthController;
use App\Core\Controllers\TopicController;
use App\Core\Controllers\PostController;

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Загрузка переменных окружения
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
    $dotenv->load();
} catch (\Exception $e) {
    die('Ошибка загрузки .env файла: ' . $e->getMessage());
}

// Настройка временной зоны
date_default_timezone_set('Asia/Yekaterinburg');

// Запуск сессии
session_start();

// Получение пути и метода
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Определяем, запрашивается ли API
$isApiRequest = strpos($path, '/api/') === 0;

try {
    // Подключение к базе данных
    $db = Database::getInstance();

    if ($isApiRequest) {
        // API маршруты
        header('Content-Type: application/json');
        $apiPath = substr($path, 4);

        switch ($apiPath) {
            case '/topics':
                if ($method === 'GET') {
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    $offset = ($page - 1) * $limit;
                    
                    $topics = $db->query("
                        SELECT t.*, u.username 
                        FROM topics t 
                        JOIN users u ON t.author_id = u.id 
                        ORDER BY t.created_at DESC 
                        LIMIT ? OFFSET ?
                    ", [$limit, $offset]);

                    echo json_encode([
                        'success' => true,
                        'data' => $topics
                    ]);
                }
                break;

            default:
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'API endpoint not found'
                ]);
        }
    } else {
        // Веб-интерфейс
        $auth = new AuthController();
        $topic = new TopicController();
        $post = new PostController();

        // Проверяем маршруты для управления темами
        if (preg_match('#^/topics/(\d+)/edit$#', $path, $matches)) {
            include __DIR__ . '/../Views/layout/header.php';
            $topic->showEditForm($matches[1]);
            include __DIR__ . '/../Views/layout/footer.php';
            exit;
        }

        if (preg_match('#^/topics/(\d+)/update$#', $path, $matches)) {
            $topic->update($matches[1]);
            exit;
        }

        if (preg_match('#^/topics/(\d+)/toggle$#', $path, $matches)) {
            $topic->toggleStatus($matches[1]);
            exit;
        }

        if (preg_match('#^/topics/(\d+)/delete$#', $path, $matches)) {
            $topic->delete($matches[1]);
            exit;
        }

        // Проверяем, является ли путь просмотром темы
        if (preg_match('#^/topics/(\d+)$#', $path, $matches)) {
            include __DIR__ . '/../Views/layout/header.php';
            $topic->show($matches[1]);
            include __DIR__ . '/../Views/layout/footer.php';
            exit;
        }

        switch ($path) {
            case '/':
                // Получаем последние темы для главной страницы
                $topics = $db->query("
                    SELECT t.*, u.username 
                    FROM topics t 
                    JOIN users u ON t.author_id = u.id 
                    ORDER BY t.created_at DESC 
                    LIMIT 10
                ");
                
                include __DIR__ . '/../Views/layout/header.php';
                include __DIR__ . '/../Views/home/index.php';
                include __DIR__ . '/../Views/layout/footer.php';
                break;

            case '/auth/login':
                if ($method === 'GET') {
                    include __DIR__ . '/../Views/layout/header.php';
                    $auth->showLoginForm();
                    include __DIR__ . '/../Views/layout/footer.php';
                } else if ($method === 'POST') {
                    $auth->login();
                }
                break;

            case '/auth/register':
                if ($method === 'GET') {
                    include __DIR__ . '/../Views/layout/header.php';
                    $auth->showRegistrationForm();
                    include __DIR__ . '/../Views/layout/footer.php';
                } else if ($method === 'POST') {
                    $auth->register();
                }
                break;

            case '/auth/logout':
                $auth->logout();
                break;

            case '/topics/create':
                if ($method === 'GET') {
                    include __DIR__ . '/../Views/layout/header.php';
                    $topic->showCreateForm();
                    include __DIR__ . '/../Views/layout/footer.php';
                } else if ($method === 'POST') {
                    $topic->create();
                }
                break;

            case '/posts/create':
                if ($method === 'POST') {
                    $post->create();
                }
                break;

            case '/swagger':
            case '/swagger/':
                header('Location: /swagger/index.html');
                exit;
                
            default:
                if (strpos($path, '/swagger/') === 0) {
                    return false;
                }
                
                http_response_code(404);
                include __DIR__ . '/../Views/layout/header.php';
                include __DIR__ . '/../Views/errors/404.php';
                include __DIR__ . '/../Views/layout/footer.php';
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    if ($isApiRequest) {
        echo json_encode([
            'success' => false,
            'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Internal Server Error'
        ]);
    } else {
        if ($_ENV['APP_DEBUG']) {
            echo 'Ошибка: ' . $e->getMessage();
        } else {
            include __DIR__ . '/../Views/layout/header.php';
            include __DIR__ . '/../Views/errors/500.php';
            include __DIR__ . '/../Views/layout/footer.php';
        }
    }
} 