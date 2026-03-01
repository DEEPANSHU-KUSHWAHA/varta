<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;

session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Clear session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// After destroying session 
require_once __DIR__ . '/../resources/flash.php'; 
set_flash("You have been logged out successfully.", "info"); 
header("Location: /public/auth.php"); 
exit;