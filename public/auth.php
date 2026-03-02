<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';
?>

<div class="auth-tabs">
    <!-- Tab headers -->
    <ul class="tab-header">
        <li class="tab-link active" data-tab="login">Login</li>
        <li class="tab-link" data-tab="signup">Sign Up</li>
    </ul>

    <!-- Login Tab -->
    <div id="login" class="tab-content active">
        <h2>Login</h2>
        <?php show_flash(); ?>
        <form method="POST" action="/api/login.php">
            <label for="login_username">Username</label>
            <input type="text" id="login_username" name="username" required autocomplete="username">

            <label for="login_password">Password</label>
            <input type="password" id="login_password" name="password" required autocomplete="current-password">

            <label for="login_otp">One-Time Password (OTP)</label>
            <input type="text" id="login_otp" name="totp" required autocomplete="one-time-code">

            <!-- Remember Me -->
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember Me</label>
            </div>

            <button type="submit">Login</button>
        </form>
        <p><a href="#" class="nav-option" data-page="reset_password">Forgot your password?</a></p>
    </div>

    <!-- Signup Tab -->
    <div id="signup" class="tab-content">
        <h2>Sign Up</h2>
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

            <label for="signup_username">Username</label>
            <input type="text" id="signup_username" name="username" required autocomplete="username">

            <label for="signup_password">Password</label>
            <input type="password" id="signup_password" name="password" required autocomplete="new-password">

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

            <label for="signup_otp">One-Time Password (OTP)</label>
            <input type="text" id="signup_otp" name="totp" required autocomplete="one-time-code">

            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? 
            <a href="#" class="tab-link" data-tab="login">Login here</a>
        </p>
    </div>
</div>

<!-- Tab Switcher Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-link');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');

            // Remove active from all
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => {
                c.classList.remove('active');
                c.classList.remove('fade-in');
            });

            // Add active to selected with animation
            tab.classList.add('active');
            const targetContent = document.getElementById(target);
            targetContent.classList.add('active', 'fade-in');
        });
    });
});
</script>
