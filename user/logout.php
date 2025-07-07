<?php
require_once '../functions.php';

// Clear all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
?>

