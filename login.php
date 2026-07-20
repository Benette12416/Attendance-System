<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Ensure session started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure $conn is available. Try to use DB constants from config if $conn wasn't set there.
if (!isset($conn) || !$conn) {
    if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$conn) {
            die('Database connection failed: ' . mysqli_connect_error());
        }
    } else {
        trigger_error('Database connection not found. Please define $conn or DB_* constants in includes/config.php', E_USER_ERROR);
    }
}

ensureDefaultAdmin($conn);

if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password, fullname FROM admins WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['fullname'];
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - RFID Attendance System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<div class="login-box">
    <h1>📡 RFID Attendance System</h1>
    <p class="subtitle">Sign in to the admin dashboard</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required autofocus>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
        <p>Test by jem</p>
    </form>
    <!--<p class="text-muted" style="text-align:center; margin-top:16px;">
        Default: admin / admin123
    </p>-->
</div>
</body>
</html>
