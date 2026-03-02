<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

// ✅ Clear all session data
$_SESSION = [];

// ✅ Destroy the session completely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// ✅ Flash message for feedback
set_flash("You have been logged out successfully.", "info");

// ✅ Redirect back to auth page
header("Location: /public/auth.php");
exit;
