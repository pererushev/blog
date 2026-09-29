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

// Явно устанавливаем кодировку
$pdo->exec("SET NAMES utf8mb4");

// Проверяем кодировку соединения
$charset = $pdo->query("SHOW VARIABLES LIKE 'character_set_client'")->fetch();
echo "Client charset: " . $charset['Value'] . "\n";

// Читаем данные
$stmt = $pdo->query("SELECT id, name, HEX(name) AS hex_name FROM categories LIMIT 5");
$results = $stmt->fetchAll();

echo "\nData from database:\n";
foreach ($results as $row) {
    echo "ID: {$row['id']}, Name: {$row['name']}, HEX: {$row['hex_name']}\n";
}