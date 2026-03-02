<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

// ✅ Protect dashboard: redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    set_flash("You must be logged in to access the dashboard.", "error");
    header("Location: /public/auth.php");
    exit;
}

// ✅ Fetch user info (optional, for display)
$stmt = $conn->prepare("SELECT username, first_name, last_login FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
/** @var array<string,mixed>|null $user */
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Varta</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Welcome to Your Dashboard</h2>

        <!-- Flash message display -->
        <?php show_flash(); ?>

        <?php if ($user): ?>
            <p>Hello, <strong><?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?></strong>!</p>
            <p>Last login: <?php echo htmlspecialchars($user['last_login']); ?></p>
        <?php endif; ?>

        <hr>

        <p><a href="profile.php">View Profile</a></p>
        <p><a href="settings.php">Account Settings</a></p>
        <p><a href="/api/logout.php">Logout</a></p>
    </div>
</body>
</html>
