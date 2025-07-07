<?php
// Database configuration for testing with SQLite
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_system');

// Application settings
define('SITE_NAME', 'Library Management System');
define('SITE_URL', 'http://localhost:8000');
define('ADMIN_EMAIL', 'admin@library.com');

// Session settings
session_start();

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection using SQLite for testing
try {
    $db_path = __DIR__ . '/database/library.db';
    $pdo = new PDO("sqlite:" . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

