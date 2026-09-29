<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Загрузка .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Инициализация Smarty
$smarty = require_once __DIR__ . '/../config/smarty.php';

// Простой роутер (пока заглушка)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Тест: проверим, что модели работают
$categoryModel = new \App\Models\Category();
$categories = $categoryModel->getAllWithPosts();

$smarty->assign('categories', $categories);
$smarty->assign('currentUri', $uri);
$smarty->display('pages/home.tpl');