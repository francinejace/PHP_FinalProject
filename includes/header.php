<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">Library System</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                        <?php elseif ($_SESSION['role'] === 'student'): ?>
                            <li><a href="student/dashboard.php">Student Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="user/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="user/login.php">Login</a></li>
                        <li><a href="user/register.php">Register</a></li>
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

