<?php
/** @var mysqli $conn */
global $conn;

$host = getenv("CPANEL_DB_HOST");
$user = getenv("CPANEL_DB_USER");
$pass = getenv("CPANEL_DB_PASS");
$db   = getenv("CPANEL_DB_NAME");

// Only connect if environment variables are set
if ($host && $user && $db) {
    $conn = new mysqli($host, $user, $pass, $db);
    
    if ($conn->connect_error) {
        // Log error but don't die - allow page to render
        error_log("Database connection failed: " . $conn->connect_error);
        $conn = null;
    }
} else {
    // Database not configured - allow page to render without DB
    $conn = null;
}

return $conn ?? null;
?>
