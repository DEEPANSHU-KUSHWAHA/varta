<?php
// public/signup.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - Varta</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
    <div class="signup-container">
        <h2>Create Account</h2>
        <form method="POST" action="/api/signup.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="new-password">

            <button type="submit">Sign Up</button>
        </form>

        <p>Already have an account? <a href="index.php?page=login">Login here</a></p>
    </div>
</body>
</html>
