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
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error_message = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        if (register_user($username, $email, $password, $full_name, 'student')) {
            $success_message = 'Registration successful! You can now log in.';
            // Clear form data
            $_POST = [];
        } else {
            $error_message = 'Username or email already exists. Please choose different ones.';
        }
    }
}

$page_title = 'Register';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library Management System</title>
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in" style="max-width: 500px;">
            <div class="login-header">
                <h1 class="login-title">📚 Join Library System</h1>
                <p style="text-align: center; color: var(--text-light); margin-bottom: 2rem;">
                    Create your student account to start borrowing books.
                </p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        class="form-input" 
                        required 
                        value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                        placeholder="Enter your full name"
                        autocomplete="name"
                    >
                </div>
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        required 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        placeholder="Choose a username"
                        autocomplete="username"
                        pattern="[a-zA-Z0-9_]{3,20}"
                        title="Username must be 3-20 characters long and contain only letters, numbers, and underscores"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        required 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        placeholder="Enter your email address"
                        autocomplete="email"
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
                        placeholder="Create a password (min. 6 characters)"
                        autocomplete="new-password"
                        minlength="6"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input" 
                        required 
                        placeholder="Confirm your password"
                        autocomplete="new-password"
                        minlength="6"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Create Account
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-light);">
                    Already have an account? 
                    <a href="login.php" style="color: var(--primary-brown); text-decoration: none; font-weight: 600;">
                        Sign in here
                    </a>
                </p>
            </div>
            
            <div style="margin-top: 2rem; padding: 1rem; background: var(--light-beige); border-radius: 8px;">
                <h4 style="color: var(--primary-brown); margin-bottom: 0.5rem;">Account Information:</h4>
                <ul style="font-size: 0.9rem; color: var(--text-light); margin-left: 1rem;">
                    <li>Student accounts can borrow up to 2 books</li>
                    <li>Borrowing period is 7 days</li>
                    <li>Late returns incur a fine of ₱10 per day</li>
                    <li>All fields are required for registration</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="/assets/script.js"></script>
    <script>
        // Focus on full name field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('full_name').focus();
        });
        
        // Real-time password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#F44336';
                this.setCustomValidity('Passwords do not match');
            } else {
                this.style.borderColor = '';
                this.setCustomValidity('');
            }
        });
        
        // Username validation
        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            const pattern = /^[a-zA-Z0-9_]{3,20}$/;
            
            if (username && !pattern.test(username)) {
                this.style.borderColor = '#F44336';
                this.setCustomValidity('Username must be 3-20 characters long and contain only letters, numbers, and underscores');
            } else {
                this.style.borderColor = '';
                this.setCustomValidity('');
            }
        });
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            // Remove existing strength indicator
            const existingIndicator = this.parentNode.querySelector('.password-strength');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            if (password.length > 0) {
                const indicator = document.createElement('div');
                indicator.className = 'password-strength';
                indicator.style.marginTop = '0.5rem';
                indicator.style.fontSize = '0.8rem';
                
                if (strength < 2) {
                    indicator.style.color = '#F44336';
                    indicator.textContent = 'Weak password';
                } else if (strength < 4) {
                    indicator.style.color = '#FF9800';
                    indicator.textContent = 'Medium password';
                } else {
                    indicator.style.color = '#4CAF50';
                    indicator.textContent = 'Strong password';
                }
                
                this.parentNode.appendChild(indicator);
            }
        });
    </script>
</body>
</html>

