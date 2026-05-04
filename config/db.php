<?php
/**
 * PDO connection for CHARGED CMS.
 * Adjust credentials for your local XAMPP / production environment.
 */

declare(strict_types=1);

if (!defined('CHARGED_ROOT')) {
    define('CHARGED_ROOT', dirname(__DIR__));
}

$dbHost = '127.0.0.1';
$dbName = 'u145584795_charged';
$dbUser = 'u145584795_charged';
$dbPass = 'Phenomenal@10';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Avoid leaking connection details in production; log instead if you add logging.
    http_response_code(500);
    exit('Database connection failed. Check config/db.php and that the database exists.');
}
