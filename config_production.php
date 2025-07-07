<?php
// Production Database configuration
// IMPORTANT: Update these values with your hosting provider's database credentials
define('DB_HOST', 'localhost'); // Your database host
define('DB_USER', 'your_db_username'); // Your database username
define('DB_PASS', 'your_db_password'); // Your database password
define('DB_NAME', 'your_db_name'); // Your database name

// Application settings
define('SITE_NAME', 'Library Management System');
define('SITE_URL', 'https://yourdomain.com'); // Update with your domain
define('ADMIN_EMAIL', 'admin@yourdomain.com'); // Update with your email

// Session settings
session_start();

// Timezone
date_default_timezone_set('Asia/Manila'); // Update with your timezone

// Error reporting (DISABLE in production)
error_reporting(0);
ini_set('display_errors', 0);

// Database connection using MySQL for production
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    // Log error instead of displaying it
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}
?>

