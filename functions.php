<?php
/**
 * Library Management System - Core Functions
 * 
 * This file contains utility functions used throughout the application.
 * Enhanced with better error handling, security, and modern PHP features.
 */

require_once 'config.php';

/**
 * Generate unique book ID based on title, publication date, category, and count
 * 
 * @param string $title Book title
 * @param string $pubDate Publication date
 * @param string $addedDate Date added to system
 * @param string $category Book category
 * @param int $count Sequential count for uniqueness
 * @return string Generated book ID
 * @throws InvalidArgumentException If required parameters are missing or invalid
 */
function generateBookID($title, $pubDate, $addedDate, $category, $count) {
    // Validate inputs
    if (empty($title) || empty($pubDate) || empty($addedDate) || empty($category)) {
        throw new InvalidArgumentException("All parameters are required for book ID generation");
    }
    
    if (!is_numeric($count) || $count < 0) {
        throw new InvalidArgumentException("Count must be a non-negative number");
    }
    
    // Extract first two characters of title (uppercase, letters only)
    $titlePrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $title), 0, 2));
    if (strlen($titlePrefix) < 2) {
        $titlePrefix = str_pad($titlePrefix, 2, 'X');
    }
    
    // Extract month abbreviation from publication date
    $pubTimestamp = strtotime($pubDate);
    if ($pubTimestamp === false) {
        throw new InvalidArgumentException("Invalid publication date format: $pubDate");
    }
    $month = strtoupper(date('M', $pubTimestamp));
    
    // Extract day from added date
    $addedTimestamp = strtotime($addedDate);
    if ($addedTimestamp === false) {
        throw new InvalidArgumentException("Invalid added date format: $addedDate");
    }
    $day = date('d', $addedTimestamp);
    
    // Extract year from publication date
    $year = date('Y', $pubTimestamp);
    
    // Sanitize category (remove non-alphanumeric characters)
    $categoryCode = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $category));
    $categoryCode = substr($categoryCode, 0, 3); // Limit to 3 characters
    if (strlen($categoryCode) < 3) {
        $categoryCode = str_pad($categoryCode, 3, 'X');
    }
    
    // Format count with leading zeros
    $countFormatted = str_pad($count, 5, '0', STR_PAD_LEFT);
    
    return "{$titlePrefix}{$month}{$day}{$year}-{$categoryCode}{$countFormatted}";
}

/**
 * Log user activity
 * 
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $details Additional details
 * @return bool Success status
 */
function logUserActivity($userId, $action, $details = '') {
    global $pdo;
    
    try {
        // Create table if it doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS user_activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                action VARCHAR(255) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_timestamp (timestamp)
            )
        ");
        
        $stmt = $pdo->prepare("
            INSERT INTO user_activity_log (user_id, action, details, ip_address, user_agent, timestamp) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        return $stmt->execute([$userId, $action, $details, $ipAddress, $userAgent]);
    } catch (PDOException $e) {
        error_log("Failed to log user activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user by ID with caching
 * 
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    global $pdo;
    static $userCache = [];
    
    // Check cache first
    if (isset($userCache[$userId])) {
        return $userCache[$userId];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        // Cache the result
        $userCache[$userId] = $user;
        
        return $user;
    } catch (PDOException $e) {
        error_log("Failed to get user by ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Get user by username
 * 
 * @param string $username Username
 * @return array|null User data or null if not found
 */
function getUserByUsername($username) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Failed to get user by username: " . $e->getMessage());
        return null;
    }
}

/**
 * Get book by ID with availability information
 * 
 * @param string $bookId Book ID
 * @return array|null Book data with availability or null if not found
 */
function getBookById($bookId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, 
                   (b.copies - COALESCE(borrowed.count, 0)) as available_copies,
                   COALESCE(borrowed.count, 0) as borrowed_count
            FROM books b
            LEFT JOIN (
                SELECT book_id, COUNT(*) as count 
                FROM borrowings 
                WHERE return_date IS NULL 
                GROUP BY book_id
            ) borrowed ON b.id = borrowed.book_id
            WHERE b.id = ? AND b.status = 'active'
        ");
        $stmt->execute([$bookId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Failed to get book by ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if book is available for borrowing
 * 
 * @param string $bookId Book ID
 * @return bool True if available, false otherwise
 */
function isBookAvailable($bookId) {
    $book = getBookById($bookId);
    return $book && $book['available_copies'] > 0;
}

/**
 * Get user's current borrowings with book details
 * 
 * @param int $userId User ID
 * @param bool $includeOverdue Include only overdue books
 * @return array Array of current borrowings
 */
function getUserBorrowings($userId, $includeOverdue = false) {
    global $pdo;
    
    try {
        $sql = "
            SELECT b.*, bk.title, bk.author, bk.isbn, bk.category,
                   DATEDIFF(CURDATE(), b.due_date) as days_overdue,
                   CASE WHEN b.due_date < CURDATE() THEN 1 ELSE 0 END as is_overdue
            FROM borrowings b 
            JOIN books bk ON b.book_id = bk.id 
            WHERE b.user_id = ? AND b.return_date IS NULL
        ";
        
        if ($includeOverdue) {
            $sql .= " AND b.due_date < CURDATE()";
        }
        
        $sql .= " ORDER BY b.due_date ASC, b.borrow_date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Failed to get user borrowings: " . $e->getMessage());
        return [];
    }
}

/**
 * Get overdue books with user information
 * 
 * @param int $limit Maximum number of results
 * @return array Array of overdue borrowings
 */
function getOverdueBooks($limit = 100) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, bk.title, bk.author, bk.isbn, u.username, u.email, u.full_name,
                   DATEDIFF(CURDATE(), b.due_date) as days_overdue
            FROM borrowings b 
            JOIN books bk ON b.book_id = bk.id 
            JOIN users u ON b.user_id = u.id 
            WHERE b.return_date IS NULL AND b.due_date < CURDATE()
            ORDER BY b.due_date ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Failed to get overdue books: " . $e->getMessage());
        return [];
    }
}

/**
 * Search books with advanced filtering
 * 
 * @param string $query Search query
 * @param array $filters Additional filters (category, author, year, etc.)
 * @param int $limit Maximum number of results
 * @param int $offset Offset for pagination
 * @return array Array of matching books with total count
 */
function searchBooks($query, $filters = [], $limit = 20, $offset = 0) {
    global $pdo;
    
    try {
        $whereConditions = ["b.status = 'active'"];
        $params = [];
        
        // Search query
        if (!empty($query)) {
            $whereConditions[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.description LIKE ?)";
            $searchTerm = "%{$query}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        // Category filter
        if (!empty($filters['category'])) {
            $whereConditions[] = "b.category = ?";
            $params[] = $filters['category'];
        }
        
        // Author filter
        if (!empty($filters['author'])) {
            $whereConditions[] = "b.author LIKE ?";
            $params[] = "%{$filters['author']}%";
        }
        
        // Year filter
        if (!empty($filters['year'])) {
            $whereConditions[] = "YEAR(b.publication_date) = ?";
            $params[] = $filters['year'];
        }
        
        // Availability filter
        if (!empty($filters['available_only'])) {
            $whereConditions[] = "(b.copies - COALESCE(borrowed.count, 0)) > 0";
        }
        
        $whereClause = implode(' AND ', $whereConditions);
        
        // Get total count
        $countSql = "
            SELECT COUNT(DISTINCT b.id) as total
            FROM books b
            LEFT JOIN (
                SELECT book_id, COUNT(*) as count 
                FROM borrowings 
                WHERE return_date IS NULL 
                GROUP BY book_id
            ) borrowed ON b.id = borrowed.book_id
            WHERE {$whereClause}
        ";
        
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Get results
        $sql = "
            SELECT b.*, 
                   (b.copies - COALESCE(borrowed.count, 0)) as available_copies,
                   COALESCE(borrowed.count, 0) as borrowed_count
            FROM books b
            LEFT JOIN (
                SELECT book_id, COUNT(*) as count 
                FROM borrowings 
                WHERE return_date IS NULL 
                GROUP BY book_id
            ) borrowed ON b.id = borrowed.book_id
            WHERE {$whereClause}
            ORDER BY b.title ASC 
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        return [
            'books' => $results,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    } catch (PDOException $e) {
        error_log("Failed to search books: " . $e->getMessage());
        return ['books' => [], 'total' => 0, 'limit' => $limit, 'offset' => $offset];
    }
}

/**
 * Get books by category with pagination
 * 
 * @param string $category Category name
 * @param int $limit Maximum number of results
 * @param int $offset Offset for pagination
 * @return array Array of books with total count
 */
function getBooksByCategory($category, $limit = 20, $offset = 0) {
    return searchBooks('', ['category' => $category], $limit, $offset);
}

/**
 * Get all book categories with book counts
 * 
 * @return array Array of categories with counts
 */
function getBookCategories() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT category, COUNT(*) as book_count 
            FROM books 
            WHERE status = 'active' 
            GROUP BY category 
            ORDER BY category ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Failed to get book categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Format date for display with localization support
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return 'Invalid Date';
    }
    
    return date($format, $timestamp);
}

/**
 * Calculate days between two dates
 * 
 * @param string $date1 First date
 * @param string $date2 Second date (defaults to today)
 * @return int Number of days (positive if date2 is after date1)
 */
function daysBetween($date1, $date2 = null) {
    if ($date2 === null) {
        $date2 = date('Y-m-d');
    }
    
    $timestamp1 = strtotime($date1);
    $timestamp2 = strtotime($date2);
    
    if ($timestamp1 === false || $timestamp2 === false) {
        return 0;
    }
    
    return (int)(($timestamp2 - $timestamp1) / (60 * 60 * 24));
}

/**
 * Redirect to a URL with proper headers
 * 
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect($url, $statusCode = 302) {
    // Prevent header injection
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    if (!headers_sent()) {
        http_response_code($statusCode);
        header("Location: $url");
    } else {
        // Fallback to JavaScript redirect if headers already sent
        echo "<script>window.location.href = '" . htmlspecialchars($url, ENT_QUOTES) . "';</script>";
    }
    exit();
}

/**
 * Display flash message
 * 
 * @param string $message Message text
 * @param string $type Message type (success, error, warning, info)
 */
function setFlashMessage($message, $type = 'info') {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][] = [
        'text' => $message,
        'type' => $type,
        'timestamp' => time()
    ];
}

/**
 * Get and optionally clear flash messages
 * 
 * @param bool $clear Whether to clear messages after getting them
 * @return array Array of flash messages
 */
function getFlashMessages($clear = true) {
    $messages = $_SESSION['flash_messages'] ?? [];
    
    if ($clear) {
        unset($_SESSION['flash_messages']);
    }
    
    return $messages;
}

/**
 * Check if user has permission for an action
 * 
 * @param string $permission Permission name
 * @param int $userId User ID (optional, defaults to current user)
 * @return bool True if user has permission
 */
function hasPermission($permission, $userId = null) {
    if ($userId === null && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    if (!$userId) {
        return false;
    }
    
    $user = getUserById($userId);
    if (!$user) {
        return false;
    }
    
    // Admin has all permissions
    if ($user['role'] === 'admin') {
        return true;
    }
    
    // Define role-based permissions
    $permissions = [
        'student' => [
            'view_books', 'borrow_books', 'view_own_borrowings', 
            'return_books', 'view_own_profile', 'update_own_profile'
        ],
        'librarian' => [
            'view_books', 'manage_books', 'view_borrowings', 'manage_borrowings',
            'view_users', 'view_reports', 'manage_categories'
        ],
        'admin' => ['*'] // All permissions
    ];
    
    $userRole = $user['role'];
    if (!isset($permissions[$userRole])) {
        return false;
    }
    
    return in_array($permission, $permissions[$userRole]) || in_array('*', $permissions[$userRole]);
}

/**
 * Require login and optionally specific permission
 * 
 * @param string $permission Required permission (optional)
 * @param string $redirectUrl URL to redirect if not authorized
 */
function requireAuth($permission = null, $redirectUrl = '/user/login.php') {
    if (!isSessionValid()) {
        setFlashMessage('Please log in to access this page.', 'warning');
        redirect($redirectUrl);
    }
    
    if ($permission && !hasPermission($permission)) {
        setFlashMessage('You do not have permission to access this page.', 'error');
        redirect('/index.php');
    }
}

/**
 * Generate pagination HTML with improved styling
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links
 * @param array $params Additional URL parameters
 * @return string HTML for pagination
 */
function generatePagination($currentPage, $totalPages, $baseUrl, $params = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav class="pagination-nav" aria-label="Pagination Navigation">';
    $html .= '<ul class="pagination">';
    
    // Build query string for additional parameters
    $queryString = '';
    if (!empty($params)) {
        $queryString = '&' . http_build_query($params);
    }
    
    // Previous page
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $html .= "<li><a href=\"{$baseUrl}?page={$prevPage}{$queryString}\" class=\"pagination-link\" aria-label=\"Previous page\">&laquo; Previous</a></li>";
    } else {
        $html .= "<li><span class=\"pagination-link disabled\" aria-label=\"Previous page (disabled)\">&laquo; Previous</span></li>";
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    // First page and ellipsis
    if ($start > 1) {
        $html .= "<li><a href=\"{$baseUrl}?page=1{$queryString}\" class=\"pagination-link\">1</a></li>";
        if ($start > 2) {
            $html .= "<li><span class=\"pagination-ellipsis\">...</span></li>";
        }
    }
    
    // Page range
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= "<li><span class=\"pagination-current\" aria-current=\"page\">{$i}</span></li>";
        } else {
            $html .= "<li><a href=\"{$baseUrl}?page={$i}{$queryString}\" class=\"pagination-link\">{$i}</a></li>";
        }
    }
    
    // Last page and ellipsis
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= "<li><span class=\"pagination-ellipsis\">...</span></li>";
        }
        $html .= "<li><a href=\"{$baseUrl}?page={$totalPages}{$queryString}\" class=\"pagination-link\">{$totalPages}</a></li>";
    }
    
    // Next page
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $html .= "<li><a href=\"{$baseUrl}?page={$nextPage}{$queryString}\" class=\"pagination-link\" aria-label=\"Next page\">Next &raquo;</a></li>";
    } else {
        $html .= "<li><span class=\"pagination-link disabled\" aria-label=\"Next page (disabled)\">Next &raquo;</span></li>";
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Validate and sanitize book data
 * 
 * @param array $data Book data to validate
 * @return array Validation result with errors
 */
function validateBookData($data) {
    $errors = [];
    $sanitized = [];
    
    // Required fields
    $requiredFields = ['title', 'author', 'isbn', 'category', 'publication_date'];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = ucfirst($field) . ' is required';
        } else {
            $sanitized[$field] = sanitizeInput($data[$field]);
        }
    }
    
    // ISBN validation
    if (!empty($data['isbn'])) {
        $isbn = preg_replace('/[^0-9X]/', '', strtoupper($data['isbn']));
        if (!preg_match('/^(\d{10}|\d{13})$/', $isbn)) {
            $errors['isbn'] = 'Invalid ISBN format';
        } else {
            $sanitized['isbn'] = $isbn;
        }
    }
    
    // Publication date validation
    if (!empty($data['publication_date'])) {
        $date = strtotime($data['publication_date']);
        if ($date === false || $date > time()) {
            $errors['publication_date'] = 'Invalid publication date';
        } else {
            $sanitized['publication_date'] = date('Y-m-d', $date);
        }
    }
    
    // Copies validation
    if (isset($data['copies'])) {
        $copies = filter_var($data['copies'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($copies === false) {
            $errors['copies'] = 'Copies must be a positive integer';
        } else {
            $sanitized['copies'] = $copies;
        }
    }
    
    // Optional fields
    $optionalFields = ['description', 'publisher', 'pages', 'language'];
    foreach ($optionalFields as $field) {
        if (isset($data[$field])) {
            $sanitized[$field] = sanitizeInput($data[$field]);
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $sanitized
    ];
}

/**
 * Generate secure random string
 * 
 * @param int $length Length of the string
 * @param string $characters Character set to use
 * @return string Random string
 */
function generateRandomString($length = 32, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Send email notification (placeholder for email functionality)
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param array $headers Additional headers
 * @return bool Success status
 */
function sendEmail($to, $subject, $message, $headers = []) {
    // This is a placeholder function
    // In a real implementation, you would use a proper email library like PHPMailer
    // or integrate with an email service like SendGrid, Mailgun, etc.
    
    $defaultHeaders = [
        'From' => ADMIN_EMAIL,
        'Reply-To' => ADMIN_EMAIL,
        'Content-Type' => 'text/html; charset=UTF-8'
    ];
    
    $headers = array_merge($defaultHeaders, $headers);
    $headerString = '';
    
    foreach ($headers as $key => $value) {
        $headerString .= "$key: $value\r\n";
    }
    
    // Log email instead of sending in development
    if (!defined('DB_HOST') || DB_HOST === 'localhost') {
        error_log("Email would be sent to: $to, Subject: $subject");
        return true;
    }
    
    return mail($to, $subject, $message, $headerString);
}
?>

