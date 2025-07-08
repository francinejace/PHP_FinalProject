<?php
/**
 * Library Management System - Production Configuration
 * 
 * This file contains production-specific database connection settings and application configuration.
 * IMPORTANT: Update these values with your hosting provider's database credentials before deployment.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Production Database Configuration
// IMPORTANT: Update these values with your hosting provider's database credentials
define('DB_HOST', 'localhost'); // Your database host (usually localhost)
define('DB_USER', 'your_db_username'); // Your database username
define('DB_PASS', 'your_db_password'); // Your database password
define('DB_NAME', 'your_db_name'); // Your database name

// Application settings
define('SITE_NAME', 'Library Management System');
define('SITE_URL', 'https://yourdomain.com'); // Update with your domain
define('ADMIN_EMAIL', 'admin@yourdomain.com'); // Update with your email

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Session settings for production
session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'domain' => '', // Set your domain if needed
    'secure' => true, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Timezone
date_default_timezone_set('Asia/Manila'); // Update with your timezone

// Error reporting (DISABLE in production)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log'); // Make sure logs directory exists

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Database connection using PDO for production
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_PERSISTENT => false, // Disable persistent connections in production
            PDO::ATTR_TIMEOUT => 30 // Set connection timeout
        ]
    );
} catch(PDOException $e) {
    // Log error instead of displaying it in production
    error_log("Database connection failed: " . $e->getMessage());
    
    // Show generic error message to users
    http_response_code(503);
    die("Service temporarily unavailable. Please try again later.");
}

// Legacy mysqli connection for backward compatibility (if needed)
$conn = null;
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    
    // Set connection timeout
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 30);
    
} catch(Exception $e) {
    error_log("Legacy database connection failed: " . $e->getMessage());
    // Don't expose mysqli connection in production if PDO is working
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
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
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
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,       // 4 iterations
        'threads' => 3          // 3 threads
    ]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = '') {
    $logEntry = date('Y-m-d H:i:s') . " - " . $event;
    if ($details) {
        $logEntry .= " - " . $details;
    }
    $logEntry .= " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
    
    error_log($logEntry, 3, __DIR__ . '/logs/security.log');
}

/**
 * Rate limiting function
 */
function checkRateLimit($action, $limit = 5, $window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $action . '_' . $ip;
    
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $now = time();
    
    // Clean old entries
    foreach ($_SESSION['rate_limit'] as $k => $data) {
        if ($now - $data['time'] > $window) {
            unset($_SESSION['rate_limit'][$k]);
        }
    }
    
    // Check current action
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'time' => $now];
        return true;
    }
    
    if ($now - $_SESSION['rate_limit'][$key]['time'] > $window) {
        $_SESSION['rate_limit'][$key] = ['count' => 1, 'time' => $now];
        return true;
    }
    
    $_SESSION['rate_limit'][$key]['count']++;
    
    if ($_SESSION['rate_limit'][$key]['count'] > $limit) {
        logSecurityEvent('Rate limit exceeded', "Action: $action, IP: $ip");
        return false;
    }
    
    return true;
}

/**
 * Validate and sanitize file uploads
 */
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $maxSize = 5242880) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'No file was uploaded'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'File is too large'];
        default:
            return ['success' => false, 'message' => 'Unknown upload error'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File is too large'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf'
    ];
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedTypes) || 
        !isset($allowedMimes[$extension]) || 
        $mimeType !== $allowedMimes[$extension]) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    return ['success' => true, 'extension' => $extension, 'mime_type' => $mimeType];
}

// Create logs directory if it doesn't exist
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Set up error handler for production
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED'
    ];
    
    $errorType = $errorTypes[$severity] ?? 'UNKNOWN';
    $logMessage = "[$errorType] $message in $file on line $line";
    
    error_log($logMessage);
    
    // Don't execute PHP internal error handler
    return true;
});

// Set up exception handler for production
set_exception_handler(function($exception) {
    error_log("Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    // Show generic error page
    http_response_code(500);
    include __DIR__ . '/error/500.html';
    exit;
});
?>

