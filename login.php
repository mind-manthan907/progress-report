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
             <svg viewBox="0 0 200 200" width="120">
                    <circle cx="100" cy="100" r="95" fill="none" stroke="red" stroke-width="2"/>
                    <circle cx="100" cy="100" r="90" fill="#1a237e"/>
                    <circle cx="100" cy="100" r="70" fill="white"/>
                    <path d="M100 45 L135 60 L135 100 C135 130 100 155 100 155 C100 155 65 130 65 100 L65 60 Z" fill="#1a237e"/>
                    <text x="100" y="105" font-size="45" text-anchor="middle" fill="#ffd700" font-weight="900" font-family="serif">JP</text>
                    <defs><path id="txtPath" d="M 100, 100 m -82, 0 a 82,82 0 1,1 164,0 a 82,82 0 1,1 -164,0" /></defs>
                    <text font-size="11" fill="white" font-weight="900" font-family="sans-serif">
                        <textPath xlink:href="#txtPath" startOffset="50%" text-anchor="middle">CHARKAILA KALWARI-BASTI (U.P.)</textPath>
                    </text>
                </svg>
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
