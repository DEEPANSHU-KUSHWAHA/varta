<?php
/** @var mysqli $conn */
$host = getenv("CPANEL_DB_HOST");
$user = getenv("CPANEL_DB_USER");
$pass = getenv("CPANEL_DB_PASS");
$db   = getenv("CPANEL_DB_NAME");

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
return $conn;


?>
