<?php
session_start();
include '../config.php';

if ($_POST) {
    $u = $_POST['username'];
    $p = $_POST['password'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$u' AND password='$p'");
    if (mysqli_num_rows($q)) {
        $user = mysqli_fetch_assoc($q);
        $_SESSION['user'] = $user;
        header("Location: ../student/dashboard.php");
    } else echo "Invalid login.";
}
?>

<form method="post">
    <h2>Login</h2>
    <input name="username" placeholder="Username" required><br>
    <input name="password" type="password" placeholder="Password" required><br>
    <button>Login</button>
</form>
