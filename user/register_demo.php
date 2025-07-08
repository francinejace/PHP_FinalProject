<?php
session_start();
include '../includes/header.php';

$success_message = '';
$error_message = '';

if ($_POST) {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    $confirm_p = trim($_POST['confirm_password'] ?? '');
    
    // Basic validation
    if (strlen($u) < 3) {
        $error_message = "Username must be at least 3 characters long.";
    } elseif (strlen($p) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($p !== $confirm_p) {
        $error_message = "Passwords do not match.";
    } else {
        $success_message = "Registration successful! (Demo mode)<br>You can now log in as <strong>" . htmlspecialchars($u) . "</strong>.";
    }
}
?>

<div class="container">
    <div class="main-content" style="max-width: 500px; margin: 2rem auto;">
        <div class="card fade-in">
            <div class="card-header" style="text-align: center;">
                <h2 class="card-title">Create Account</h2>
                <p style="color: var(--text-light); margin: 0;">Join our library community today</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <div style="margin-top: 1rem;">
                        <a href="login_demo.php" class="btn btn-primary">Go to Login</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success_message): ?>
            <form method="post" action="register_demo.php">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" name="username" placeholder="Choose a username"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-input" name="password" placeholder="Create a password (min. 6 characters)" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-input" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                    Create Account
                </button>
                
                <div style="text-align: center; color: var(--text-light);">
                    <p>Already have an account? <a href="login_demo.php" style="color: var(--primary-brown); text-decoration: none; font-weight: 600;">Sign in here</a></p>
                    <p><a href="../index.php" style="color: var(--text-light); text-decoration: none;">← Back to Home</a></p>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
