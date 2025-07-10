<?php
session_start();
include '../config.php';
include '../includes/header.php';

$success_message = '';
$error_message = '';

if ($_POST) {
    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_p = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    // Basic validation
    if (strlen($u) < 3) {
        $error_message = "Username must be at least 3 characters long.";
    } elseif (strlen($p) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($p !== $confirm_p) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if username already exists
        $check_query = mysqli_query($conn, "SELECT * FROM users WHERE username='$u'");
        if (mysqli_num_rows($check_query) > 0) {
            $error_message = "Username already exists. Please choose a different one.";
        } else {
            // Hash the password
            $hashed_password = password_hash($p, PASSWORD_DEFAULT);
            
            // Get the role_id for 'student' (assuming 'student' role has id 3 from previous database import)
            // It's better to fetch this dynamically if roles can change, but for now, hardcoding for quick fix
            $role_id = 3; // Assuming 'student' role has ID 3 based on library_mysql.sql

            // Insert new user using prepared statement
            $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role_id, full_name, email) VALUES (?, ?, ?, '', '')");
            $stmt->bind_param("ssi", $u, $hashed_password, $role_id);
            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now log in with your credentials.";
            } else {
                $error_message = "Registration failed. Please try again. " . $stmt->error;
            }
            $stmt->close();
        }
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
                    <?php echo htmlspecialchars($success_message); ?>
                    <div style="margin-top: 1rem;">
                        <a href="login.php" class="btn btn-primary">Go to Login</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$success_message): ?>
            <form method="post" action="register.php">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" name="username" placeholder="Choose a username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
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
                    <p>Already have an account? <a href="login.php" style="color: var(--primary-brown); text-decoration: none; font-weight: 600;">Sign in here</a></p>
                    <p><a href="../index.php" style="color: var(--text-light); text-decoration: none;">← Back to Home</a></p>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


