<?php
session_start();

// No-cache: prevent back-button access after logout
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

include 'db.php';

// Already logged in → go to dashboard
if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $query = mysqli_query($conn,
        "SELECT * FROM users
         WHERE email='$email' AND password='$password' AND role='admin' AND status='Approved'
         LIMIT 1"
    );

    if (mysqli_num_rows($query) > 0) {
        $admin = mysqli_fetch_assoc($query);

        session_regenerate_id(true);
        $_SESSION['admin']      = $admin['email'];
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['fullname'];

        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — MR D.I.Y</title>
    <link rel="stylesheet" href="style.css">
    <style>body{margin:0;}</style>
</head>
<body>
<div class="login-page">
    <div class="login-card">

        <div class="login-logo">
            <div class="brand-badge"><img src="a.png" style="width: 100%; height: auto;"></div>
        </div>

        <div class="login-title">Welcome back</div>
        <p class="login-sub">Sign in to your admin account</p>

        <?php if ($error): ?>
            <div class="alert alert-danger">&#9888; <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control"
                       placeholder="admin@gmail.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary btn-lg"
                    style="width:100%;justify-content:center;margin-top:8px;">
                Sign In
            </button>
        </form>

    </div>
</div>
</body>
</html>
