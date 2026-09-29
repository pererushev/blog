<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$pdo = new PDO(
    "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']};charset=utf8mb4",
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$pdo->exec("SET NAMES utf8mb4");

echo "=== Categories ===\n\n";
$stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY id");
foreach ($stmt->fetchAll() as $row) {
    echo "ID: {$row['id']}\n";
    echo "Name: {$row['name']}\n";
    echo "Slug: {$row['slug']}\n";
    echo "URL: http://localhost:8080/category/{$row['slug']}\n";
    echo str_repeat('-', 50) . "\n";
}

echo "\n=== Posts (first 10) ===\n\n";
$stmt = $pdo->query("SELECT id, title, slug, views FROM posts ORDER BY id LIMIT 10");
foreach ($stmt->fetchAll() as $row) {
    echo "ID: {$row['id']}\n";
    echo "Title: {$row['title']}\n";
    echo "Slug: {$row['slug']}\n";
    echo "Views: {$row['views']}\n";
    echo "URL: http://localhost:8080/post/{$row['slug']}\n";
    echo str_repeat('-', 50) . "\n";
}