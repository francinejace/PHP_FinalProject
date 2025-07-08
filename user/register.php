<?php
include '../config.php';
if ($_POST) {
    $u = $_POST['username'];
    $p = $_POST['password'];
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$u', '$p', 'student')");
    echo "Registered! <a href='login.php'>Login</a>";
}
?>

<form method="post">
    <h2>Register</h2>
    <input name="username" placeholder="Username" required><br>
    <input name="password" type="password" placeholder="Password" required><br>
    <button>Register</button>
</form>
