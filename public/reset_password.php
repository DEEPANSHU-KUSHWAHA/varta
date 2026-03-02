<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';
?>

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

    <p>Remembered your password? 
        <a href="#" class="nav-option" data-page="auth">Login here</a>
    </p>
    <p>Don’t have an account? 
        <a href="#" class="nav-option" data-page="auth">Sign up here</a>
    </p>
</div>
