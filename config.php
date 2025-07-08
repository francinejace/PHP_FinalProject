<?php
/**
 * Library Management System - Database Configuration
 * 
 * This file contains database connection settings and application configuration.
 * For production deployment, update the database credentials below.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Environment detection
$is_production = isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost';

// Database configuration
if ($is_production) {
    // Production Database Configuration
    // IMPORTANT: Update these values with your hosting provider's database credentials
    define('DB_HOST', 'localhost');
    define('DB_USER', 'your_db_username');
    define('DB_PASS', 'your_db_password');
    define('DB_NAME', 'your_db_name');
    
    // Disable error reporting in production
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    // Development Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'library_system');
    
    // Enable error reporting in development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Application settings
define('SITE_NAME', 'Library Management System');
define('SITE_URL', $is_production ? 'https://yourdomain.com' : 'http://localhost');
define('ADMIN_EMAIL', $is_production ? 'admin@yourdomain.com' : 'admin@localhost');

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Timezone
date_default_timezone_set('Asia/Manila');

// Database connection using PDO (recommended)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch(PDOException $e) {
    if ($is_production) {
        // Log error instead of displaying it in production
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please contact the administrator.");
    } else {
        // Show detailed error in development
        die("Database connection failed: " . $e->getMessage());
    }
}

// Legacy mysqli connection for backward compatibility (if needed)
$conn = null;
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch(Exception $e) {
    if ($is_production) {
        error_log("Legacy database connection failed: " . $e->getMessage());
    } else {
        error_log("Legacy database connection failed: " . $e->getMessage());
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Check if user session is valid
 */
function isSessionValid() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Check session timeout
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>

