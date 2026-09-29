<?php

declare(strict_types=1);

// Автозагрузка Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Загрузка переменных окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Простая проверка, что всё работает
echo '<h1>Blog is running!</h1>';
echo '<p>PHP version: ' . PHP_VERSION . '</p>';
echo '<p>Environment: ' . ($_ENV['APP_ENV'] ?? 'not set') . '</p>';