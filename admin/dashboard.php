<?php

// Include configuration and functions
require_once '../config.php';
require_once '../functions.php';

// Require admin authentication
requireAuth('admin');

// Get flash messages
$flashMessages = getFlashMessages();

// Include header
include '../includes/header.php';
?>

<!-- Flash Messages -->
<?php if (!empty($flashMessages)): ?>
<div class="flash-messages">
    <?php foreach ($flashMessages as $message): ?>
    <div class="flash-message flash-<?php echo htmlspecialchars($message['type']); ?>" role="alert">
        <?php echo htmlspecialchars($message['text']); ?>
        <button type="button" class="flash-close">&times;</button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="container">
    <div class="admin-header">
        <h2>Admin Dashboard</h2>
        <div class="admin-actions">
            <a href="../user/logout.php" class="btn btn-outline">Logout</a>
        </div>
    </div>
    
    <div class="dashboard-content">
        <p>Welcome to the admin dashboard, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Administrator'); ?>.</p>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="manage_books.php" class="btn btn-primary">Manage Books</a>
            <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
            <a href="borrowings.php" class="btn btn-primary">View Borrowings</a>
            <a href="reports.php" class="btn btn-primary">Reports</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
