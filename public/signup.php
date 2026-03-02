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
    <title>Signup - Varta</title>
    <link rel="stylesheet" href="css/signup.css">
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
    <div class="signup-container">
        <h2>Create Account</h2>

        <!-- Flash message display -->
        <?php show_flash(); ?>

        <form method="POST" action="/api/signup.php" enctype="multipart/form-data">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="middle_name">Middle Name</label>
            <input type="text" id="middle_name" name="middle_name">

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name">

            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone">

            <label for="email">Email</label>
            <input type="email" id="email" name="email">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">

            <label for="avatar">Avatar</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">

            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="user">User</option>
                <option value="moderator">Moderator</option>
                <option value="admin">Admin</option>
            </select>

            <label for="totp">One-Time Password (OTP)</label>
            <input type="text" id="totp" name="totp" required autocomplete="one-time-code">

            <button type="submit">Sign Up</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
