<?php
session_start();
include '../includes/header.php';

$error_message = '';

// Demo functionality without database
if ($_POST) {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    
    // Demo credentials
    if ($u === 'admin' && $p === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'admin';
        $success_message = "Login successful! (Demo mode)";
    } elseif ($u === 'student' && $p === 'password') {
        $_SESSION['user_id'] = 2;
        $_SESSION['username'] = 'student';
        $_SESSION['role'] = 'student';
        $success_message = "Login successful! (Demo mode)";
    } else {
        $error_message = "Invalid username or password. Try: admin/admin123 or student/password";
    }
}
?>

<div class="container">
    <div class="main-content" style="max-width: 500px; margin: 2rem auto;">
        <div class="card fade-in">
            <div class="card-header" style="text-align: center;">
                <h2 class="card-title">Welcome Back</h2>
                <p style="color: var(--text-light); margin: 0;">Sign in to your account to continue</p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="login_demo.php">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" name="username" placeholder="Enter your username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-input" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                    Sign In
                </button>
                
                <div style="text-align: center; color: var(--text-light);">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Admin: admin / admin123</p>
                    <p>Student: student / password</p>
                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border-color);">
                    <p>Don't have an account? <a href="register_demo.php" style="color: var(--primary-brown); text-decoration: none; font-weight: 600;">Create one here</a></p>
                    <p><a href="../index.php" style="color: var(--text-light); text-decoration: none;">← Back to Home</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

