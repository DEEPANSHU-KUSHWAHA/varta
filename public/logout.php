<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logged Out - Varta</title>
    <link rel="stylesheet" href="css/auth.css">
    <meta http-equiv="refresh" content="3;url=index.php?page=login">
</head>
<body>
    <div class="auth-container logout">
        <h2>You have been logged out</h2>
        <p>Redirecting you to the login page...</p>
        <p><a href="index.php?page=login">Click here if you are not redirected</a></p>
    </div>
</body>
</html>
