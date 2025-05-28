<?php

require __DIR__ . '/../../vendor/autoload.php';

// Загрузка переменных окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// Запуск сессии
session_start();

// Базовая обработка ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Маршрутизация
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Простой роутер
$routes = [
    '/' => ['HomeController', 'index'],
    '/auth/login' => ['AuthController', 'login'],
    '/auth/register' => ['AuthController', 'register'],
    '/auth/logout' => ['AuthController', 'logout'],
    '/topics/create' => ['TopicController', 'create'],
    '/topics/(\d+)' => ['TopicController', 'view'],
];

// Поиск маршрута
$handler = null;
$params = [];

foreach ($routes as $pattern => $controller) {
    $pattern = str_replace('/', '\/', $pattern);
    if (preg_match('/^' . $pattern . '$/', $uri, $matches)) {
        $handler = $controller;
        array_shift($matches);
        $params = $matches;
        break;
    }
}

if ($handler) {
    $controllerName = "App\\Controllers\\" . $handler[0];
    $actionName = $handler[1];

    $controller = new $controllerName();
    call_user_func_array([$controller, $actionName], $params);
} else {
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
} 