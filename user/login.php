<?php
require_once '../functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: /admin/dashboard.php');
    } else {
        header('Location: /student/dashboard.php');
    }
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        if (login_user($username, $password)) {
            // Redirect based on role
            if (is_admin()) {
                header('Location: /admin/dashboard.php');
            } else {
                header('Location: /student/dashboard.php');
            }
            exit;
        } else {
            $error_message = 'Invalid username or password.';
        }
    }
}

$page_title = 'Login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in">
            <div class="login-header">
                <h1 class="login-title">📚 Library System</h1>
                <p style="text-align: center; color: var(--text-light); margin-bottom: 2rem;">
                    Welcome back! Please sign in to your account.
                </p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        required 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        placeholder="Enter your username"
                        autocomplete="username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Sign In
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-light);">
                    Don't have an account? 
                    <a href="register.php" style="color: var(--primary-brown); text-decoration: none; font-weight: 600;">
                        Register here
                    </a>
                </p>
            </div>
            
            <div style="margin-top: 2rem; padding: 1rem; background: var(--light-beige); border-radius: 8px;">
                <h4 style="color: var(--primary-brown); margin-bottom: 0.5rem;">Demo Accounts:</h4>
                <p style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.5rem;">
                    <strong>Admin:</strong> admin / password
                </p>
                <p style="font-size: 0.9rem; color: var(--text-light);">
                    <strong>Student:</strong> Register a new account
                </p>
            </div>
        </div>
    </div>
    
    <script src="/assets/script.js"></script>
    <script>
        // Focus on username field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Add enter key support
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>

