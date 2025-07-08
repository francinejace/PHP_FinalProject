<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php
    // Determine the correct path to assets based on current directory
    $current_dir = dirname($_SERVER['PHP_SELF']);
    if (strpos($current_dir, '/user') !== false || strpos($current_dir, '/admin') !== false || strpos($current_dir, '/student') !== false) {
        $assets_path = '../assets/style.css';
    } else {
        $assets_path = 'assets/style.css';
    }
    ?>
    <link rel="stylesheet" href="<?php echo $assets_path; ?>">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <?php
            // Determine the correct path to index based on current directory
            if (strpos($current_dir, '/user') !== false || strpos($current_dir, '/admin') !== false || strpos($current_dir, '/student') !== false) {
                $home_path = '../index.php';
                $login_path = 'login.php';
                $register_path = 'register.php';
                $logout_path = 'logout.php';
                $admin_path = '../admin/dashboard.php';
                $student_path = '../student/dashboard.php';
            } else {
                $home_path = 'index.php';
                $login_path = 'user/login.php';
                $register_path = 'user/register.php';
                $logout_path = 'user/logout.php';
                $admin_path = 'admin/dashboard.php';
                $student_path = 'student/dashboard.php';
            }
            ?>
            <a href="<?php echo $home_path; ?>" class="logo">Library System</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="<?php echo $home_path; ?>">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a href="<?php echo $admin_path; ?>">Admin Dashboard</a></li>
                        <?php elseif ($_SESSION['role'] === 'student'): ?>
                            <li><a href="<?php echo $student_path; ?>">Student Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $logout_path; ?>">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $login_path; ?>">Login</a></li>
                        <li><a href="<?php echo $register_path; ?>">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main>
