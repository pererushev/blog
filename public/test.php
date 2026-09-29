<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "PHP is working<br>";
echo "ENV loaded: " . ($_ENV['APP_ENV'] ?? 'not set') . "<br>";

try {
    $smarty = require_once __DIR__ . '/../config/smarty.php';
    echo "Smarty initialized<br>";
    
    $smarty->assign('test', 'Hello');
    $smarty->display('pages/home.tpl');
    echo "Template rendered<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}