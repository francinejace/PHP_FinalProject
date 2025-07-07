<?php
require_once '../functions.php';
require_login();

// Get user's borrowing statistics
$user_id = $_SESSION['user_id'];
$active_borrowings = get_user_borrowings($user_id, 'borrowed');
$overdue_borrowings = get_user_borrowings($user_id, 'overdue');
$returned_borrowings = get_user_borrowings($user_id, 'returned');

// Get recent books
$recent_books = get_books('', '', 'available', 6, 0);

$page_title = 'Student Dashboard';
require_once '../includes/header.php';
?>

<div class="main-content fade-in">
    <div class="card-header">
        <h1 class="card-title">🏠 Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
        <p style="color: var(--text-light);">Discover and borrow books from our extensive library collection.</p>
    </div>
    
    <!-- User Statistics -->
    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($active_borrowings); ?></div>
            <div class="stat-label">Currently Borrowed</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number" style="color: var(--error);"><?php echo count($overdue_borrowings); ?></div>
            <div class="stat-label">Overdue Books</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number"><?php echo count($returned_borrowings); ?></div>
            <div class="stat-label">Books Returned</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number"><?php echo 2 - count($active_borrowings) - count($overdue_borrowings); ?></div>
            <div class="stat-label">Available Slots</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🚀 Quick Actions</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="browse.php" class="btn btn-primary" style="text-align: center; padding: 1.5rem;">
                🔍 Browse Books
            </a>
            <a href="borrow.php" class="btn btn-secondary" style="text-align: center; padding: 1.5rem;">
                📖 My Borrowed Books
            </a>
            <a href="return.php" class="btn btn-info" style="text-align: center; padding: 1.5rem;">
                ↩️ Return Books
            </a>
        </div>
    </div>
    
    <!-- Current Borrowings -->
    <?php if (!empty($active_borrowings) || !empty($overdue_borrowings)): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📚 Your Current Books</h2>
            </div>
            
            <?php foreach (array_merge($active_borrowings, $overdue_borrowings) as $borrowing): ?>
                <?php
                $due_date = strtotime($borrowing['due_date']);
                $current_date = time();
                $is_overdue = $current_date > $due_date;
                $days_diff = ceil(($due_date - $current_date) / (60 * 60 * 24));
                ?>
                <div class="book-card" style="border-left-color: <?php echo $is_overdue ? 'var(--error)' : 'var(--primary-brown)'; ?>;">
                    <div class="book-title"><?php echo htmlspecialchars($borrowing['title']); ?></div>
                    <div class="book-author">by <?php echo htmlspecialchars($borrowing['author']); ?></div>
                    <div class="book-details">
                        <span class="book-detail">
                            📅 Borrowed: <?php echo format_date($borrowing['borrow_date']); ?>
                        </span>
                        <span class="book-detail">
                            ⏰ Due: <?php echo format_date($borrowing['due_date']); ?>
                        </span>
                        <?php if ($is_overdue): ?>
                            <span class="status-badge status-overdue">
                                <?php echo abs($days_diff); ?> days overdue
                            </span>
                            <span class="book-detail" style="color: var(--error);">
                                💰 Fine: ₱<?php echo number_format(abs($days_diff) * 10, 2); ?>
                            </span>
                        <?php else: ?>
                            <span class="status-badge status-borrowed">
                                <?php echo $days_diff; ?> days left
                            </span>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 1rem;">
                        <a href="return.php?id=<?php echo $borrowing['id']; ?>" class="btn btn-info">
                            Return Book
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Available Books -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📖 Recently Added Books</h2>
        </div>
        
        <?php if (empty($recent_books)): ?>
            <p style="text-align: center; color: var(--text-light); padding: 2rem;">
                No books available at the moment.
            </p>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                <?php foreach ($recent_books as $book): ?>
                    <div class="book-card">
                        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                        <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                        <div class="book-details">
                            <span class="book-detail"><?php echo htmlspecialchars($book['category']); ?></span>
                            <span class="book-detail"><?php echo $book['publication_year']; ?></span>
                            <span class="status-badge status-<?php echo $book['status']; ?>">
                                <?php echo ucfirst($book['status']); ?>
                            </span>
                        </div>
                        <?php if ($book['description']): ?>
                            <p style="margin: 1rem 0; color: var(--text-light); font-size: 0.9rem;">
                                <?php echo htmlspecialchars(substr($book['description'], 0, 100)); ?>
                                <?php if (strlen($book['description']) > 100) echo '...'; ?>
                            </p>
                        <?php endif; ?>
                        <div style="margin-top: 1rem;">
                            <?php if ($book['status'] === 'available' && can_user_borrow($user_id)): ?>
                                <button onclick="borrowBook(<?php echo $book['id']; ?>)" class="btn btn-primary">
                                    Borrow Book
                                </button>
                            <?php elseif (!can_user_borrow($user_id)): ?>
                                <span class="btn btn-secondary" style="opacity: 0.6; cursor: not-allowed;">
                                    Borrowing Limit Reached
                                </span>
                            <?php else: ?>
                                <span class="btn btn-secondary" style="opacity: 0.6; cursor: not-allowed;">
                                    Not Available
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="browse.php" class="btn btn-secondary">
                    Browse All Books
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Library Rules -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📋 Library Rules & Information</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div style="background: var(--light-beige); padding: 1rem; border-radius: 8px;">
                <h4 style="color: var(--primary-brown); margin-bottom: 0.5rem;">Borrowing Rules</h4>
                <ul style="color: var(--text-light); font-size: 0.9rem;">
                    <li>Maximum 2 books at a time</li>
                    <li>7-day borrowing period</li>
                    <li>Renewals not allowed</li>
                    <li>Return on or before due date</li>
                </ul>
            </div>
            
            <div style="background: var(--light-beige); padding: 1rem; border-radius: 8px;">
                <h4 style="color: var(--primary-brown); margin-bottom: 0.5rem;">Fines & Penalties</h4>
                <ul style="color: var(--text-light); font-size: 0.9rem;">
                    <li>₱10 per day for overdue books</li>
                    <li>Fines must be paid before borrowing</li>
                    <li>Lost books: replacement cost + ₱50</li>
                    <li>Damaged books: repair cost</li>
                </ul>
            </div>
            
            <div style="background: var(--light-beige); padding: 1rem; border-radius: 8px;">
                <h4 style="color: var(--primary-brown); margin-bottom: 0.5rem;">Contact Information</h4>
                <p style="color: var(--text-light); font-size: 0.9rem;">
                    <strong>Library Hours:</strong> 8:00 AM - 6:00 PM<br>
                    <strong>Email:</strong> library@school.edu<br>
                    <strong>Phone:</strong> (123) 456-7890<br>
                    <strong>Location:</strong> Main Building, 2nd Floor
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Borrow book function
function borrowBook(bookId) {
    if (!confirm('Are you sure you want to borrow this book?')) {
        return;
    }
    
    // Simple form submission for now
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'borrow.php';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'book_id';
    input.value = bookId;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Auto-refresh for overdue status
setInterval(function() {
    const overdueElements = document.querySelectorAll('.status-overdue');
    if (overdueElements.length > 0) {
        location.reload();
    }
}, 60000); // Check every minute
</script>

<?php require_once '../includes/footer.php'; ?>

