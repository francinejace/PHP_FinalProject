<?php 
/**
 * Library Management System - Main Index Page
 * 
 * This is the main landing page for the library management system.
 * It provides an overview of the system and navigation to different sections.
 * Enhanced with better security, error handling, and user experience.
 */

// Include configuration and functions
require_once 'config.php';
require_once 'functions.php';

// Security: Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Check if includes directory exists, if not create basic structure
if (!file_exists('includes/header.php')) {
    // Create includes directory
    if (!is_dir('includes')) {
        mkdir('includes', 0755, true);
    }
    
    // Create basic header if it doesn't exist
    $headerContent = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="' . htmlspecialchars(SITE_NAME) . ' - Modern library management system">
    <meta name="keywords" content="library, books, management, system, borrowing">
    <meta name="author" content="' . htmlspecialchars(SITE_NAME) . '">
    <title>' . htmlspecialchars(SITE_NAME) . '</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/style.css">
    
    <!-- Security headers via meta tags -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="' . generateCSRFToken() . '">
</head>
<body>
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="nav-container">
            <a href="index.php" class="nav-brand" aria-label="' . htmlspecialchars(SITE_NAME) . ' Home">
                ' . htmlspecialchars(SITE_NAME) . '
            </a>
            <div class="nav-menu">
                ' . (isSessionValid() ? 
                    '<span class="nav-user">Welcome, ' . htmlspecialchars($_SESSION['username'] ?? 'User') . '</span>
                     <a href="user/logout.php" class="nav-link">Logout</a>' : 
                    '<a href="user/login.php" class="nav-link">Login</a>
                     <a href="user/register.php" class="nav-link">Register</a>') . '
            </div>
        </div>
    </nav>
    <main role="main">';
    
    file_put_contents('includes/header.php', $headerContent);
}

if (!file_exists('includes/footer.php')) {
    $footerContent = '    </main>
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>' . htmlspecialchars(SITE_NAME) . '</h4>
                    <p>Modern library management system for the digital age.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        ' . (isSessionValid() ? 
                            '<li><a href="books/browse.php">Browse Books</a></li>' : 
                            '<li><a href="user/login.php">Login</a></li>') . '
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: ' . htmlspecialchars(ADMIN_EMAIL) . '</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; ' . date('Y') . ' ' . htmlspecialchars(SITE_NAME) . '. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="assets/script.js"></script>
    
    <!-- Analytics placeholder -->
    <!-- Add your analytics code here -->
</body>
</html>';
    file_put_contents('includes/footer.php', $footerContent);
}

// Get flash messages if any
$flashMessages = getFlashMessages();

// Get statistics for display with error handling
$stats = [
    'total_books' => 0,
    'total_users' => 0,
    'active_borrowings' => 0,
    'overdue_books' => 0
];

try {
    // Total books
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM books WHERE status = 'active'");
    $stmt->execute();
    $stats['total_books'] = $stmt->fetch()['total'] ?? 0;
    
    // Total users (excluding admins)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role != 'admin' AND status = 'active'");
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch()['total'] ?? 0;
    
    // Active borrowings
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM borrowings WHERE return_date IS NULL");
    $stmt->execute();
    $stats['active_borrowings'] = $stmt->fetch()['total'] ?? 0;
    
    // Overdue books
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM borrowings WHERE return_date IS NULL AND due_date < CURDATE()");
    $stmt->execute();
    $stats['overdue_books'] = $stmt->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    error_log("Failed to get statistics: " . $e->getMessage());
    // Stats remain at default values
}

// Get recent activity for display
$recentActivity = [];
if (isSessionValid() && hasPermission('view_borrowings')) {
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, bk.title, u.username, u.full_name
            FROM borrowings b 
            JOIN books bk ON b.book_id = bk.id 
            JOIN users u ON b.user_id = u.id 
            WHERE b.borrow_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY b.borrow_date DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $recentActivity = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Failed to get recent activity: " . $e->getMessage());
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Flash Messages -->
<?php if (!empty($flashMessages)): ?>
<div class="flash-messages">
    <?php foreach ($flashMessages as $message): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($message['type']); ?>" 
         data-auto-hide="5000" role="alert" aria-live="polite">
        <?php echo htmlspecialchars($message['text']); ?>
        <button type="button" class="flash-close" aria-label="Close message">&times;</button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero-section" role="banner">
    <div class="hero-content fade-in">
        <h1>Welcome to <?php echo htmlspecialchars(SITE_NAME); ?></h1>
        <p class="hero-description">
            Discover, manage, and explore our comprehensive collection of books and resources. 
            Your gateway to knowledge starts here with our modern, secure, and user-friendly library management system.
        </p>
        
        <?php if (!isSessionValid()): ?>
            <div class="hero-actions">
                <a href="user/login.php" class="btn btn-primary" aria-label="Login to your account">
                    Login to Your Account
                </a>
                <a href="user/register.php" class="btn btn-outline" aria-label="Create new account">
                    Create New Account
                </a>
            </div>
        <?php else: ?>
            <div class="hero-actions">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
                <?php elseif ($_SESSION['role'] === 'student'): ?>
                    <a href="student/dashboard.php" class="btn btn-primary">Go to Student Dashboard</a>
                <?php elseif ($_SESSION['role'] === 'librarian'): ?>
                    <a href="librarian/dashboard.php" class="btn btn-primary">Go to Librarian Dashboard</a>
                <?php endif; ?>
                <a href="books/browse.php" class="btn btn-outline">Browse Books</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Main Content -->
<div class="container">
    <div class="main-content scale-in">
        
        <!-- Statistics Grid -->
        <section class="stats-section" aria-labelledby="stats-heading">
            <h2 id="stats-heading" class="sr-only">Library Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" aria-label="Total books available">
                        <?php echo number_format($stats['total_books']); ?>+
                    </div>
                    <div class="stat-label">Books Available</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" aria-label="Active members">
                        <?php echo number_format($stats['total_users']); ?>+
                    </div>
                    <div class="stat-label">Active Members</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" aria-label="Current borrowings">
                        <?php echo number_format($stats['active_borrowings']); ?>
                    </div>
                    <div class="stat-label">Current Borrowings</div>
                </div>
                <div class="stat-card <?php echo $stats['overdue_books'] > 0 ? 'stat-warning' : ''; ?>">
                    <div class="stat-number" aria-label="Overdue books">
                        <?php echo number_format($stats['overdue_books']); ?>
                    </div>
                    <div class="stat-label">Overdue Books</div>
                </div>
            </div>
        </section>
        
        <!-- About Section -->
        <section class="about-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">About Our Library System</h3>
                </div>
                <div class="card-body">
                    <p>
                        Our modern library management system provides seamless access to a vast collection of books and resources. 
                        Whether you're a student looking for academic materials or an administrator managing the collection, 
                        our platform offers intuitive tools and features to enhance your library experience.
                    </p>
                    
                    <h4>Key Features:</h4>
                    <div class="features-grid">
                        <div class="feature-item">
                            <span class="feature-icon" aria-hidden="true">🔍</span>
                            <span class="feature-text">Advanced book search and discovery</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" aria-hidden="true">📚</span>
                            <span class="feature-text">User-friendly borrowing system</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" aria-hidden="true">⏰</span>
                            <span class="feature-text">Real-time availability tracking</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" aria-hidden="true">⚙️</span>
                            <span class="feature-text">Comprehensive admin tools</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" aria-hidden="true">📱</span>
                            <span class="feature-text">Responsive design for all devices</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" aria-hidden="true">🔒</span>
                            <span class="feature-text">Secure user authentication</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" aria-hidden="true">📊</span>
                            <span class="feature-text">Advanced reporting capabilities</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon" aria-hidden="true">🔔</span>
                            <span class="feature-text">Automated notifications</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Quick Actions (for logged-in users) -->
        <?php if (isSessionValid()): ?>
        <section class="quick-actions-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="books/browse.php" class="quick-action-btn" aria-label="Browse available books">
                            <span class="action-icon" aria-hidden="true">📚</span>
                            <span class="action-text">Browse Books</span>
                        </a>
                        
                        <?php if ($_SESSION['role'] === 'student'): ?>
                        <a href="student/borrowings.php" class="quick-action-btn" aria-label="View my borrowings">
                            <span class="action-icon" aria-hidden="true">📖</span>
                            <span class="action-text">My Borrowings</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('manage_books')): ?>
                        <a href="admin/add_book.php" class="quick-action-btn" aria-label="Add new book">
                            <span class="action-icon" aria-hidden="true">➕</span>
                            <span class="action-text">Add Book</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (hasPermission('view_borrowings')): ?>
                        <a href="admin/borrowings.php" class="quick-action-btn" aria-label="Manage borrowings">
                            <span class="action-icon" aria-hidden="true">📋</span>
                            <span class="action-text">Manage Borrowings</span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($stats['overdue_books'] > 0 && hasPermission('view_borrowings')): ?>
                        <a href="admin/overdue.php" class="quick-action-btn quick-action-warning" aria-label="View overdue books">
                            <span class="action-icon" aria-hidden="true">⚠️</span>
                            <span class="action-text">Overdue Books</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- Recent Activity -->
        <section class="recent-activity-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Activity</h3>
                </div>
                <div class="card-body">
                    <?php if (isSessionValid() && hasPermission('view_borrowings') && !empty($recentActivity)): ?>
                        <div class="activity-list" role="list">
                            <?php foreach ($recentActivity as $activity): ?>
                            <div class="activity-item" role="listitem">
                                <div class="activity-user">
                                    <?php echo htmlspecialchars($activity['full_name'] ?: $activity['username']); ?>
                                </div>
                                <div class="activity-action">borrowed</div>
                                <div class="activity-book">
                                    <?php echo htmlspecialchars($activity['title']); ?>
                                </div>
                                <div class="activity-date">
                                    <time datetime="<?php echo $activity['borrow_date']; ?>">
                                        <?php echo formatDate($activity['borrow_date']); ?>
                                    </time>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (isSessionValid()): ?>
                        <p>No recent activity to display.</p>
                    <?php else: ?>
                        <p>
                            <a href="user/login.php">Login</a> to view recent library activity and access more features.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- System Status (for admins) -->
        <?php if (isSessionValid() && $_SESSION['role'] === 'admin'): ?>
        <section class="system-status-section">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">System Status</h3>
                </div>
                <div class="card-body">
                    <div class="status-grid">
                        <div class="status-item">
                            <span class="status-label">Database:</span>
                            <span class="status-value status-ok">Connected</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Session:</span>
                            <span class="status-value status-ok">Active</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">PHP Version:</span>
                            <span class="status-value"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Server Time:</span>
                            <span class="status-value"><?php echo date('Y-m-d H:i:s'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
    </div>
</div>

<!-- Additional CSS for enhanced styling -->
<style>
/* Enhanced Hero Section */
.hero-section {
    background: linear-gradient(135deg, #8B4513 0%, #A0522D 100%);
    color: white;
    padding: 4rem 2rem;
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="books" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><rect width="20" height="20" fill="none"/><rect x="2" y="8" width="3" height="10" fill="rgba(255,255,255,0.1)"/><rect x="6" y="6" width="3" height="12" fill="rgba(255,255,255,0.1)"/><rect x="10" y="7" width="3" height="11" fill="rgba(255,255,255,0.1)"/><rect x="14" y="5" width="3" height="13" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23books)"/></svg>') repeat;
    opacity: 0.1;
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
}

.hero-content h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hero-description {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    line-height: 1.6;
    opacity: 0.95;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Enhanced Statistics */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(139, 69, 19, 0.1);
    text-align: center;
    border-left: 4px solid #8B4513;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(139, 69, 19, 0.2);
}

.stat-card.stat-warning {
    border-left-color: #ffc107;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #8B4513;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stat-warning .stat-number {
    color: #ffc107;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.feature-item:hover {
    background: #e9ecef;
}

.feature-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.feature-text {
    font-weight: 500;
    color: #333;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.quick-action-btn:hover {
    background: #8B4513;
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(139, 69, 19, 0.3);
    text-decoration: none;
}

.quick-action-warning {
    background: #fff3cd;
    border-color: #ffc107;
}

.quick-action-warning:hover {
    background: #ffc107;
    color: #000;
}

.action-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.action-text {
    font-weight: 500;
    text-align: center;
    font-size: 0.9rem;
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.activity-item {
    display: grid;
    grid-template-columns: auto 1fr auto auto;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #8B4513;
}

.activity-user {
    font-weight: 600;
    color: #8B4513;
}

.activity-action {
    color: #666;
    font-style: italic;
}

.activity-book {
    font-weight: 500;
    color: #333;
}

.activity-date {
    font-size: 0.85rem;
    color: #999;
    white-space: nowrap;
}

/* System Status */
.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.status-label {
    font-weight: 500;
    color: #666;
}

.status-value {
    font-weight: 600;
}

.status-ok {
    color: #28a745;
}

/* Flash Messages */
.flash-messages {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 1000;
    max-width: 400px;
}

.flash-message {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 6px;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    animation: slideIn 0.3s ease-out;
}

.flash-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0;
    margin-left: 1rem;
    opacity: 0.7;
}

.flash-close:hover {
    opacity: 1;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Screen reader only content */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .hero-description {
        font-size: 1rem;
    }
    
    .hero-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .activity-item {
        grid-template-columns: 1fr;
        gap: 0.5rem;
        text-align: center;
    }
    
    .flash-messages {
        left: 1rem;
        right: 1rem;
        max-width: none;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

