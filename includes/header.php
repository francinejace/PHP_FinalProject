<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Library Management System</title>
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/" class="logo">Library System</a>
            
            <?php if (is_logged_in()): ?>
                <nav>
                    <ul class="nav-menu">
                        <?php if (is_admin()): ?>
                            <li><a href="/admin/dashboard.php">Dashboard</a></li>
                            <li><a href="/admin/manage.php">Manage Books</a></li>
                            <li><a href="/admin/add.php">Add Book</a></li>
                            <li><a href="/admin/users.php">Users</a></li>
                        <?php else: ?>
                            <li><a href="/student/dashboard.php">Dashboard</a></li>
                            <li><a href="/student/browse.php">Browse Books</a></li>
                            <li><a href="/student/borrow.php">My Books</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
                    <span class="status-badge status-<?php echo $_SESSION['role']; ?>">
                        <?php echo ucfirst($_SESSION['role']); ?>
                    </span>
                    <a href="/user/logout.php" class="btn btn-secondary">Logout</a>
                </div>
            <?php else: ?>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="/user/login.php">Login</a></li>
                        <li><a href="/user/register.php">Register</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </header>
    
    <main class="container"><?php
        // Display flash messages
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        
        if (isset($_SESSION['warning_message'])) {
            echo '<div class="alert alert-warning">' . htmlspecialchars($_SESSION['warning_message']) . '</div>';
            unset($_SESSION['warning_message']);
        }
        
        if (isset($_SESSION['info_message'])) {
            echo '<div class="alert alert-info">' . htmlspecialchars($_SESSION['info_message']) . '</div>';
            unset($_SESSION['info_message']);
        }
    ?>

