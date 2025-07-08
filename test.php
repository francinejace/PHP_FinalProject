<?php

// Include configuration and functions
require_once 'config.php';
require_once 'functions.php';

// Security check - only allow access in development or for admins
if (!defined('DB_HOST') || (DB_HOST !== 'localhost' && (!isSessionValid() || !hasPermission('admin')))) {
    http_response_code(404);
    die('Page not found');
}

// Generate CSRF token for forms
$csrfToken = generateCSRFToken();

// Test data for demonstration
$testData = [
    'buttons' => [
        ['text' => 'Primary Button', 'class' => 'btn-primary'],
        ['text' => 'Secondary Button', 'class' => 'btn-secondary'],
        ['text' => 'Success Button', 'class' => 'btn-success'],
        ['text' => 'Warning Button', 'class' => 'btn-warning'],
        ['text' => 'Danger Button', 'class' => 'btn-danger'],
        ['text' => 'Outline Button', 'class' => 'btn-outline']
    ],
    'alerts' => [
        ['text' => 'This is a success message!', 'type' => 'success'],
        ['text' => 'This is an info message!', 'type' => 'info'],
        ['text' => 'This is a warning message!', 'type' => 'warning'],
        ['text' => 'This is an error message!', 'type' => 'danger']
    ],
    'sampleBooks' => [
        ['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'isbn' => '9780743273565', 'status' => 'Available'],
        ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'isbn' => '9780061120084', 'status' => 'Borrowed'],
        ['title' => '1984', 'author' => 'George Orwell', 'isbn' => '9780451524935', 'status' => 'Available'],
        ['title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'isbn' => '9780141439518', 'status' => 'Overdue']
    ]
];

include 'includes/header.php'; 
?>

<div class="container">
    <div class="main-content">
        
        <!-- Page Header -->
        <header class="page-header">
            <h1>Test Page</h1>
            <p class="page-description">
                This page demonstrates the modernized styling and components of the Library Management System. 
                It showcases various UI elements, responsive design, and accessibility features.
            </p>
        </header>

        <!-- Alert Messages -->
        <section class="test-section" aria-labelledby="alerts-heading">
            <h2 id="alerts-heading">Alert Messages</h2>
            <div class="alerts-demo">
                <?php foreach ($testData['alerts'] as $alert): ?>
                <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?>" role="alert">
                    <?php echo htmlspecialchars($alert['text']); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Buttons -->
        <section class="test-section" aria-labelledby="buttons-heading">
            <h2 id="buttons-heading">Button Styles</h2>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Button Variations</h3>
                </div>
                <div class="card-body">
                    <div class="button-grid">
                        <?php foreach ($testData['buttons'] as $button): ?>
                        <button type="button" class="btn <?php echo htmlspecialchars($button['class']); ?>">
                            <?php echo htmlspecialchars($button['text']); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <h4>Button Sizes</h4>
                    <div class="button-sizes">
                        <button type="button" class="btn btn-primary btn-sm">Small Button</button>
                        <button type="button" class="btn btn-primary">Regular Button</button>
                        <button type="button" class="btn btn-primary btn-lg">Large Button</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Forms -->
        <section class="test-section" aria-labelledby="forms-heading">
            <h2 id="forms-heading">Form Elements</h2>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sample Form</h3>
                </div>
                <div class="card-body">
                    <form class="test-form" data-validate novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="form-group">
                            <label for="test-name" class="form-label">Full Name *</label>
                            <input type="text" id="test-name" name="name" class="form-control" 
                                placeholder="Enter your full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="test-email" class="form-label">Email Address *</label>
                            <input type="email" id="test-email" name="email" class="form-control" 
                                placeholder="Enter your email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="test-role" class="form-label">Role</label>
                            <select id="test-role" name="role" class="form-control form-select">
                                <option value="">Select a role</option>
                                <option value="student">Student</option>
                                <option value="librarian">Librarian</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="test-message" class="form-label">Message</label>
                            <textarea id="test-message" name="message" class="form-control" rows="4" 
                                    placeholder="Enter your message"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="test-terms" name="terms" class="form-check-input">
                                <label for="test-terms" class="form-label">I agree to the terms and conditions</label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Form</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Tables -->
        <section class="test-section" aria-labelledby="tables-heading">
            <h2 id="tables-heading">Data Tables</h2>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sample Book Listing</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table" role="table">
                            <thead>
                                <tr>
                                    <th data-sort="text">Title</th>
                                    <th data-sort="text">Author</th>
                                    <th data-sort="text">ISBN</th>
                                    <th data-sort="text">Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($testData['sampleBooks'] as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($book['status']); ?>">
                                            <?php echo htmlspecialchars($book['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary">View</button>
                                        <button type="button" class="btn btn-sm btn-secondary">Edit</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Grid System -->
        <section class="test-section" aria-labelledby="grid-heading">
            <h2 id="grid-heading">Grid System</h2>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Responsive Grid Layout</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="grid-demo-item">
                                <h4>Column 1</h4>
                                <p>This is a responsive column that adapts to different screen sizes.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="grid-demo-item">
                                <h4>Column 2</h4>
                                <p>Grid system ensures proper layout across all devices.</p>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-4">
                            <div class="grid-demo-item">
                                <h4>Column 3</h4>
                                <p>Flexible and easy to use for complex layouts.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pagination -->
        <section class="test-section" aria-labelledby="pagination-heading">
            <h2 id="pagination-heading">Pagination</h2>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pagination Example</h3>
                </div>
                <div class="card-body">
                    <?php echo generatePagination(3, 10, '/test.php', ['search' => 'example']); ?>
                </div>
            </div>
        </section>

        <!-- Accessibility Features -->
        <section class="test-section" aria-labelledby="accessibility-heading">
            <h2 id="accessibility-heading">Accessibility Features</h2>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">WCAG Compliance</h3>
                </div>
                <div class="card-body">
                    <ul class="accessibility-list">
                        <li>✅ Semantic HTML structure with proper headings</li>
                        <li>✅ ARIA labels and roles for screen readers</li>
                        <li>✅ Keyboard navigation support</li>
                        <li>✅ High contrast color scheme</li>
                        <li>✅ Focus indicators for interactive elements</li>
                        <li>✅ Alternative text for images</li>
                        <li>✅ Form labels properly associated</li>
                        <li>✅ Skip links for navigation</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Performance Info -->
        <section class="test-section" aria-labelledby="performance-heading">
            <h2 id="performance-heading">Performance Information</h2>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">System Performance</h3>
                </div>
                <div class="card-body">
                    <div class="performance-grid">
                        <div class="performance-item">
                            <span class="performance-label">PHP Version:</span>
                            <span class="performance-value"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="performance-item">
                            <span class="performance-label">Memory Usage:</span>
                            <span class="performance-value"><?php echo round(memory_get_usage() / 1024 / 1024, 2); ?> MB</span>
                        </div>
                        <div class="performance-item">
                            <span class="performance-label">Peak Memory:</span>
                            <span class="performance-value"><?php echo round(memory_get_peak_usage() / 1024 / 1024, 2); ?> MB</span>
                        </div>
                        <div class="performance-item">
                            <span class="performance-label">Server Time:</span>
                            <span class="performance-value"><?php echo date('Y-m-d H:i:s'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>

<!-- Additional CSS for test page -->
<style>
.page-header {
    text-align: center;
    margin-bottom: 3rem;
    padding: 2rem 0;
    border-bottom: 2px solid #e9ecef;
}

.page-header h1 {
    color: #8B4513;
    margin-bottom: 1rem;
}

.page-description {
    font-size: 1.1rem;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

.test-section {
    margin-bottom: 3rem;
}

.test-section h2 {
    color: #8B4513;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #8B4513;
}

.alerts-demo {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.button-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.button-sizes {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.test-form {
    max-width: 600px;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-available {
    background: #d4edda;
    color: #155724;
}

.status-borrowed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-overdue {
    background: #f8d7da;
    color: #721c24;
}

.grid-demo-item {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid #8B4513;
}

.grid-demo-item h4 {
    color: #8B4513;
    margin-bottom: 0.5rem;
}

.accessibility-list {
    list-style: none;
    padding: 0;
}

.accessibility-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.accessibility-list li:last-child {
    border-bottom: none;
}

.performance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.performance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #8B4513;
}

.performance-label {
    font-weight: 500;
    color: #666;
}

.performance-value {
    font-weight: 600;
    color: #8B4513;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .button-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .button-sizes {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .performance-grid {
        grid-template-columns: 1fr;
    }
    
    .performance-item {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
}

/* Animation for demonstration */
.test-section {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Focus styles for accessibility */
.btn:focus,
.form-control:focus,
.form-check-input:focus {
    outline: 2px solid #8B4513;
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .status-badge {
        border: 2px solid currentColor;
    }
    
    .grid-demo-item {
        border: 2px solid #8B4513;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .test-section {
        animation: none;
    }
    
    * {
        transition: none !important;
    }
}
</style>

<script>
// Test JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Form validation demo
    const testForm = document.querySelector('.test-form');
    if (testForm) {
        testForm.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Form validation passed! (This is just a demo)');
        });
    }
    
    // Table sorting demo
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.title = 'Click to sort';
    });
    
    // Button click feedback
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.type || this.type !== 'submit') {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }
        });
    });
    
    console.log('Test page JavaScript loaded successfully!');
});
</script>

<?php include 'includes/footer.php'; ?>

