<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Varta</title>
    <link rel="stylesheet" href="css/reset.css">
    <style>
        .flash {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
        }
        .flash.info { background: #e7f3fe; color: #31708f; }
        .flash.success { background: #dff0d8; color: #3c763d; }
        .flash.error { background: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Your Password</h2>

        <!-- Flash message display -->
        <?php show_flash(); ?>

        <form method="POST" action="/api/reset_password.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username">

            <label for="totp">One-Time Password (OTP)</label>
            <input type="text" id="totp" name="totp" required autocomplete="one-time-code">

            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required autocomplete="new-password">

            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">

            <button type="submit">Reset Password</button>
        </form>

        <p>Remembered your password? <a href="login.php">Login here</a></p>
        <p>Don’t have an account? <a href="signup.php">Sign up here</a></p>
    </div>
</body>
</html>
