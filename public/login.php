<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';
?>

<div class="login-container">
    <h2>Login</h2>

    <!-- Flash message display -->
    <?php show_flash(); ?>

    <form method="POST" action="/api/login.php">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autocomplete="username">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <label for="otp">One-Time Password (OTP)</label>
        <input type="text" id="otp" name="totp" required autocomplete="one-time-code">

        <button type="submit">Login</button>
    </form>

    <p>Don’t have an account? 
        <a href="#" class="nav-option" data-page="auth">Sign up here</a>
    </p>
    <p>
        <a href="#" class="nav-option" data-page="reset_password">Forgot your password?</a>
    </p>
</div>
