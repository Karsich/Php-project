<?php

use App\Core\Controllers\TopicController;
use App\Core\Controllers\PostController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Topics routes
    $app->group('/topics', function (RouteCollectorProxy $group) {
        $group->get('', [TopicController::class, 'getTopics']);
        $group->post('', [TopicController::class, 'createTopic'])->add(new AuthMiddleware());
    });

    // Posts routes
    $app->group('/posts', function (RouteCollectorProxy $group) {
        $group->get('', [PostController::class, 'getPosts']);
        $group->post('', [PostController::class, 'createPost'])->add(new AuthMiddleware());
    });
}; 