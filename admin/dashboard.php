<?php
session_start();
include '../config.php';
if ($_SESSION['user']['role'] != 'admin') header("Location: ../user/login.php");

echo "<h2>Admin Dashboard</h2>";
echo "<a href='../user/logout.php'>Logout</a>";
?>
