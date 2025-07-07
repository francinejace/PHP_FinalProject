<?php
require_once 'config.php';

// User Authentication Functions
function login_user($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        return true;
    }
    return false;
}

function register_user($username, $email, $password, $full_name, $role = 'student') {
    global $pdo;
    
    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return false; // User already exists
    }
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$username, $email, $password_hash, $full_name, $role]);
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function logout_user() {
    session_destroy();
    header('Location: /user/login.php');
    exit;
}

// Book Management Functions
function generate_book_id($title, $category, $publication_month, $publication_year) {
    global $pdo;
    
    // Get first 2 letters from title (uppercase)
    $title_prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $title), 0, 2));
    
    // Month abbreviations
    $months = [
        1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR', 5 => 'MAY', 6 => 'JUN',
        7 => 'JUL', 8 => 'AUG', 9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC'
    ];
    $month_abbr = $months[$publication_month] ?? 'JAN';
    
    // Day (10 - fixed as per requirement)
    $day = '10';
    
    // Year
    $year = $publication_year;
    
    // Category (3 letters uppercase)
    $category_code = strtoupper(substr($category, 0, 3));
    
    // Count existing books to generate sequence number
    $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    $existing_count = $stmt->fetchColumn();
    $sequence = str_pad($existing_count + 1, 5, '0', STR_PAD_LEFT);
    
    return "{$title_prefix}{$month_abbr}{$day}{$year}-{$category_code}{$sequence}";
}

function add_book($title, $author, $category, $isbn, $publication_year, $publication_month, $description = '') {
    global $pdo;
    
    $book_id = generate_book_id($title, $category, $publication_month, $publication_year);
    
    $stmt = $pdo->prepare("INSERT INTO books (book_id, title, author, category, isbn, publication_year, publication_month, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$book_id, $title, $author, $category, $isbn, $publication_year, $publication_month, $description]);
}

function get_books($search = '', $category = '', $status = '', $limit = 10, $offset = 0) {
    global $pdo;
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(title LIKE ? OR author LIKE ? OR book_id LIKE ?)";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($category)) {
        $where_conditions[] = "category = ?";
        $params[] = $category;
    }
    
    if (!empty($status)) {
        $where_conditions[] = "status = ?";
        $params[] = $status;
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    $sql = "SELECT * FROM books {$where_clause} ORDER BY date_added DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_book_by_id($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function update_book($id, $title, $author, $category, $isbn, $publication_year, $publication_month, $description) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, isbn = ?, publication_year = ?, publication_month = ?, description = ? WHERE id = ?");
    return $stmt->execute([$title, $author, $category, $isbn, $publication_year, $publication_month, $description, $id]);
}

function archive_book($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE books SET status = 'archived' WHERE id = ?");
    return $stmt->execute([$id]);
}

function delete_book($id) {
    global $pdo;
    
    // Check if book can be deleted (not archived and no active borrowings)
    $book = get_book_by_id($id);
    if (!$book || $book['status'] === 'archived') {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE book_id = ? AND status = 'borrowed'");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        return false; // Book has active borrowings
    }
    
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    return $stmt->execute([$id]);
}

// Borrowing Functions
function can_user_borrow($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status = 'borrowed'");
    $stmt->execute([$user_id]);
    $active_borrowings = $stmt->fetchColumn();
    
    return $active_borrowings < 2; // Maximum 2 books
}

function borrow_book($user_id, $book_id) {
    global $pdo;
    
    // Check if user can borrow more books
    if (!can_user_borrow($user_id)) {
        return false;
    }
    
    // Check if book is available
    $book = get_book_by_id($book_id);
    if (!$book || $book['status'] !== 'available') {
        return false;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Create borrowing record
        $due_date = date('Y-m-d H:i:s', strtotime('+7 days')); // 7 days from now
        $stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, due_date) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $book_id, $due_date]);
        
        // Update book status
        $stmt = $pdo->prepare("UPDATE books SET status = 'borrowed' WHERE id = ?");
        $stmt->execute([$book_id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function return_book($borrowing_id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Get borrowing record
        $stmt = $pdo->prepare("SELECT * FROM borrowings WHERE id = ? AND status = 'borrowed'");
        $stmt->execute([$borrowing_id]);
        $borrowing = $stmt->fetch();
        
        if (!$borrowing) {
            $pdo->rollback();
            return false;
        }
        
        // Calculate fine if overdue
        $return_date = date('Y-m-d H:i:s');
        $fine_amount = 0;
        
        if (strtotime($return_date) > strtotime($borrowing['due_date'])) {
            $days_overdue = ceil((strtotime($return_date) - strtotime($borrowing['due_date'])) / (60 * 60 * 24));
            $fine_amount = $days_overdue * 10.00; // P 10.00 per day
        }
        
        // Update borrowing record
        $stmt = $pdo->prepare("UPDATE borrowings SET return_date = ?, status = 'returned', fine_amount = ? WHERE id = ?");
        $stmt->execute([$return_date, $fine_amount, $borrowing_id]);
        
        // Update book status
        $stmt = $pdo->prepare("UPDATE books SET status = 'available' WHERE id = ?");
        $stmt->execute([$borrowing['book_id']]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function get_user_borrowings($user_id, $status = '') {
    global $pdo;
    
    $where_clause = "WHERE b.user_id = ?";
    $params = [$user_id];
    
    if (!empty($status)) {
        $where_clause .= " AND b.status = ?";
        $params[] = $status;
    }
    
    $sql = "SELECT b.*, bk.title, bk.author, bk.book_id 
            FROM borrowings b 
            JOIN books bk ON b.book_id = bk.id 
            {$where_clause} 
            ORDER BY b.borrow_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_all_borrowings($status = '', $limit = 20, $offset = 0) {
    global $pdo;
    
    $where_clause = "";
    $params = [];
    
    if (!empty($status)) {
        $where_clause = "WHERE b.status = ?";
        $params[] = $status;
    }
    
    $sql = "SELECT b.*, u.username, u.full_name, bk.title, bk.author, bk.book_id 
            FROM borrowings b 
            JOIN users u ON b.user_id = u.id 
            JOIN books bk ON b.book_id = bk.id 
            {$where_clause} 
            ORDER BY b.borrow_date DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Update overdue status
function update_overdue_books() {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE borrowings SET status = 'overdue' WHERE due_date < datetime('now') AND status = 'borrowed'");
    return $stmt->execute();
}

// Utility Functions
function get_categories() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll();
}

function get_book_stats() {
    global $pdo;
    
    $stats = [];
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    $stats['total_books'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'available'");
    $stats['available_books'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'borrowed'");
    $stats['borrowed_books'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'archived'");
    $stats['archived_books'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $stats['total_students'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'overdue'");
    $stats['overdue_books'] = $stmt->fetchColumn();
    
    return $stats;
}

function format_date($date) {
    return date('M d, Y', strtotime($date));
}

function format_datetime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

// Security function
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Check if user is authorized for admin functions
function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        header('Location: /user/login.php');
        exit;
    }
}

// Check if user is logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: /user/login.php');
        exit;
    }
}
?>

