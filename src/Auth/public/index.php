<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Auth\Controllers\AuthController;

// Загрузка переменных окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Заголовки CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Роутинг
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$controller = new AuthController();

switch ($path) {
    case '/register':
        $controller->register();
        break;
    case '/login':
        $controller->login();
        break;
    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Not Found']);
} 