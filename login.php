<?php
session_start();
$error = "";
$admin_username = "admin";
$admin_password = "password123";

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    if ($user === $admin_username && $pass === $admin_password) {
        $_SESSION['logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid Username or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - J.P. ACADEMY</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-wrapper">
    <div class="login-box">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="logo.png" alt="J.P. Academy Logo" style="width: 120px; height: 120px; object-fit: contain;">
        </div>
        <h2 style="text-align: center; color: var(--primary); margin-bottom: 25px;">Admin Login</h2>
        <?php if ($error): ?>
            <div style="color: red; text-align: center; margin-bottom: 15px; font-weight: bold;"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter Username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter Password">
            </div>
            <button type="submit" name="login" class="btn btn-view" style="width: 100%; padding: 12px; margin-top: 10px;">Login to Reports</button>
        </form>
    </div>
</body>
</html>
