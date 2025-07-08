<?php
session_start();
include '../config.php';
if (!isset($_SESSION['user'])) header("Location: ../user/login.php");

echo "<h2>Student Dashboard</h2>";
echo "Welcome, " . $_SESSION['user']['username'];
?>
<p><a href="../user/logout.php">Logout</a></p>
