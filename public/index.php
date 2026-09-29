<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$smarty = require_once __DIR__ . '/../config/smarty.php';

// Простой роутер
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Разбор маршрута
$parts = explode('/', trim($uri, '/'));
$route = $parts[0] ?? '';
$slug = $parts[1] ?? '';

try {
    if ($uri === '/') {
        (new \App\Controllers\HomeController($smarty))->index();
    } elseif ($route === 'category' && $slug) {
        (new \App\Controllers\CategoryController($smarty))->show($slug);
    } elseif ($route === 'post' && $slug) {
        (new \App\Controllers\PostController($smarty))->show($slug);
    } else {
        throw new Exception('404 Not Found');
    }
} catch (Exception $e) {
    http_response_code(404);
    echo '<h1>404 - Страница не найдена</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<p><a href="/">Вернуться на главную</a></p>';
    
    // В dev-режиме показываем стек
    if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
}