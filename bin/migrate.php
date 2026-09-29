<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $migrationFile = __DIR__ . '/../database/migrations/001_create_tables.sql';
    $sql = file_get_contents($migrationFile);

    $pdo->exec($sql);

    echo "✅ Migration executed successfully!\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}