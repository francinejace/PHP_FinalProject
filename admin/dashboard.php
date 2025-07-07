<?php
require_once '../functions.php';
require_admin();

// Update overdue books
update_overdue_books();

// Get statistics
$stats = get_book_stats();

// Get recent borrowings
$recent_borrowings = get_all_borrowings('', 5, 0);

// Get overdue books
$overdue_borrowings = get_all_borrowings('overdue', 10, 0);

$page_title = 'Admin Dashboard';
require_once '../includes/header.php';
?>

<div class="main-content fade-in">
    <div class="card-header">
        <h1 class="card-title">📊 Admin Dashboard</h1>
        <p style="color: var(--text-light);">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! Here's your library overview.</p>
    </div>
    
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_books']; ?></div>
            <div class="stat-label">Total Books</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['available_books']; ?></div>
            <div class="stat-label">Available Books</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['borrowed_books']; ?></div>
            <div class="stat-label">Borrowed Books</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total_students']; ?></div>
            <div class="stat-label">Registered Students</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number" style="color: var(--error);"><?php echo $stats['overdue_books']; ?></div>
            <div class="stat-label">Overdue Books</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['archived_books']; ?></div>
            <div class="stat-label">Archived Books</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🚀 Quick Actions</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="add.php" class="btn btn-primary" style="text-align: center; padding: 1.5rem;">
                ➕ Add New Book
            </a>
            <a href="manage.php" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                📚 Manage Books
            </a>
            <a href="users.php" class="btn btn-info" style="text-align: center; padding: 1.5rem;">
                👥 Manage Users
            </a>
            <a href="archive.php" class="btn btn-warning" style="text-align: center; padding: 1.5rem;">
                📦 View Archive
            </a>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
        <!-- Recent Borrowings -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📖 Recent Borrowings</h2>
            </div>
            
            <?php if (empty($recent_borrowings)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                    No recent borrowings found.
                </p>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($recent_borrowings as $borrowing): ?>
                        <div class="book-card" style="margin-bottom: 1rem;">
                            <div class="book-title" style="font-size: 1rem;">
                                <?php echo htmlspecialchars($borrowing['title']); ?>
                            </div>
                            <div class="book-author" style="font-size: 0.9rem;">
                                by <?php echo htmlspecialchars($borrowing['author']); ?>
                            </div>
                            <div class="book-details">
                                <span class="book-detail">
                                    👤 <?php echo htmlspecialchars($borrowing['full_name']); ?>
                                </span>
                                <span class="book-detail">
                                    📅 <?php echo format_date($borrowing['borrow_date']); ?>
                                </span>
                                <span class="status-badge status-<?php echo $borrowing['status']; ?>">
                                    <?php echo ucfirst($borrowing['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="manage.php?tab=borrowings" class="btn btn-secondary">
                        View All Borrowings
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Overdue Books -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title" style="color: var(--error);">⚠️ Overdue Books</h2>
            </div>
            
            <?php if (empty($overdue_borrowings)): ?>
                <p style="text-align: center; color: var(--success); padding: 2rem;">
                    🎉 No overdue books! Great job!
                </p>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($overdue_borrowings as $borrowing): ?>
                        <?php
                        $days_overdue = ceil((time() - strtotime($borrowing['due_date'])) / (60 * 60 * 24));
                        $fine = $days_overdue * 10;
                        ?>
                        <div class="book-card" style="margin-bottom: 1rem; border-left-color: var(--error);">
                            <div class="book-title" style="font-size: 1rem;">
                                <?php echo htmlspecialchars($borrowing['title']); ?>
                            </div>
                            <div class="book-author" style="font-size: 0.9rem;">
                                by <?php echo htmlspecialchars($borrowing['author']); ?>
                            </div>
                            <div class="book-details">
                                <span class="book-detail">
                                    👤 <?php echo htmlspecialchars($borrowing['full_name']); ?>
                                </span>
                                <span class="book-detail" style="color: var(--error);">
                                    ⏰ <?php echo $days_overdue; ?> days overdue
                                </span>
                                <span class="book-detail" style="color: var(--error);">
                                    💰 ₱<?php echo number_format($fine, 2); ?> fine
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="manage.php?tab=overdue" class="btn btn-danger">
                        Manage Overdue Books
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- System Information -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h2 class="card-title">ℹ️ System Information</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div style="background: var(--light-beige); padding: 1rem; border-radius: 8px;">
                <h4 style="color: var(--primary-brown); margin-bottom: 0.5rem;">Library Rules</h4>
                <ul style="color: var(--text-light); font-size: 0.9rem;">
                    <li>Maximum 2 books per student</li>
                    <li>7-day borrowing period</li>
                    <li>₱10 fine per day for overdue books</li>
                    <li>Minimum 50 books in collection</li>
                </ul>
            </div>
            
            <div style="background: var(--light-beige); padding: 1rem; border-radius: 8px;">
                <h4 style="color: var(--primary-brown); margin-bottom: 0.5rem;">Book ID Format</h4>
                <p style="color: var(--text-light); font-size: 0.9rem;">
                    <strong>Format:</strong> TTMMMDDYYYY-CCC#####<br>
                    <strong>Example:</strong> THFEB102022-FIC00001<br>
                    <em>TT = Title prefix, MMM = Month, DD = Day (10), YYYY = Year, CCC = Category, ##### = Sequence</em>
                </p>
            </div>
            
            <div style="background: var(--light-beige); padding: 1rem; border-radius: 8px;">
                <h4 style="color: var(--primary-brown); margin-bottom: 0.5rem;">Current Status</h4>
                <p style="color: var(--text-light); font-size: 0.9rem;">
                    <strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                    <strong>Database:</strong> Connected ✅<br>
                    <strong>Last Update:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh statistics every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);

// Add hover effects to stat cards
document.addEventListener('DOMContentLoaded', function() {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.05)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>

