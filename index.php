<?php 
session_start();
include 'includes/header.php';
?>

<section class="hero-section">
    <div class="hero-content fade-in">
        <h1>Welcome to the Library System</h1>
        <p>Discover, manage, and explore our comprehensive collection of books and resources. Your gateway to knowledge starts here.</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="user/login.php" class="btn btn-primary">Login to Your Account</a>
                <a href="user/register.php" class="btn btn-outline">Create New Account</a>
            </div>
        <?php else: ?>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
                <?php elseif ($_SESSION['role'] === 'student'): ?>
                    <a href="student/dashboard.php" class="btn btn-primary">Go to Student Dashboard</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="container">
    <div class="main-content scale-in">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">1,000+</div>
                <div class="stat-label">Books Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">500+</div>
                <div class="stat-label">Active Members</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Online Access</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">About Our Library System</h3>
            </div>
            <p>Our modern library management system provides seamless access to a vast collection of books and resources. Whether you're a student looking for academic materials or an administrator managing the collection, our platform offers intuitive tools and features to enhance your library experience.</p>
            
            <h4>Key Features:</h4>
            <ul style="margin-left: 2rem; color: var(--text-light);">
                <li>Easy book search and discovery</li>
                <li>User-friendly borrowing system</li>
                <li>Real-time availability tracking</li>
                <li>Comprehensive admin tools</li>
                <li>Responsive design for all devices</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

