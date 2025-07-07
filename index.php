<?php
require_once 'functions.php';

// Update overdue books status
update_overdue_books();

// Redirect based on user role
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: /admin/dashboard.php');
    } else {
        header('Location: /student/dashboard.php');
    }
    exit;
} else {
    header('Location: /user/login.php');
    exit;
}
?>
