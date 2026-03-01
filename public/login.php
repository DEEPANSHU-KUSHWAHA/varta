<?php
// public/login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Varta</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST" action="/api/login.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <label for="otp">One-Time Password (OTP)</label>
            <input type="text" id="otp" name="otp" required autocomplete="one-time-code">

            <button type="submit">Login</button>
        </form>

        <p>Don’t have an account? <a href="index.php?page=signup">Sign up here</a></p>
    </div>
</body>
</html>
