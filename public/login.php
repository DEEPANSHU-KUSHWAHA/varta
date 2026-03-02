<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';
?>

<div class="auth-wrapper">
    <div class="auth-box">
        <h2>Login</h2>

        <!-- Flash message display -->
        <?php show_flash(); ?>

        <form method="POST" action="/api/login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username" placeholder="Username or email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Password">
            </div>

            <div class="form-group">
                <label for="otp">One-Time Password (OTP)</label>
                <input type="text" id="otp" name="totp" required autocomplete="one-time-code" placeholder="6‑digit code">
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p>Don’t have an account? 
            <a href="#" class="nav-option" data-page="auth">Sign up here</a>
        </p>
        <p>
            <a href="#" class="nav-option" data-page="reset_password">Forgot your password?</a>
        </p>
    </div>
</div>
