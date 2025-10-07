<?php
// database connection
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_NAME = getenv('DB_NAME') ?: 'neighborhoodhub';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "<h1>Database connection failed</h1>";
    echo "<pre>".htmlspecialchars($e->getMessage())."</pre>";
    exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();

if (session_status() === PHP_SESSION_NONE) session_start();

if (!function_exists('h')) {
    function h(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

function logged_in(): bool {
    return !empty($_SESSION['user_id']);
}

function require_login(): void {
    if (!logged_in()) {
        header('Location: index.php'); // send to login page
        exit;
    }
}
