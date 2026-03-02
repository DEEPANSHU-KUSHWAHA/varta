<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

// ✅ Protect profile: redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    set_flash("You must be logged in to view your profile.", "error");
    header("Location: /public/auth.php");
    exit;
}

// ✅ Fetch user info
$stmt = $conn->prepare("SELECT username, email, phone, first_name, middle_name, last_name, role, avatar_path, last_login 
                        FROM users WHERE id = ? LIMIT 1");
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
    <title>Profile - Varta</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Your Profile</h2>

        <!-- Flash message display -->
        <?php show_flash(); ?>

        <?php if ($user): ?>
            <?php if (!empty($user['avatar_path'])): ?>
                <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" alt="Avatar" style="width:100px;height:100px;border-radius:50%;margin-bottom:15px;">
            <?php endif; ?>

            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars(trim($user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'])); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
            <p><strong>Last Login:</strong> <?php echo htmlspecialchars($user['last_login']); ?></p>
        <?php else: ?>
            <p>User details not found.</p>
        <?php endif; ?>

        <hr>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
        <p><a href="settings.php">Edit Account Settings</a></p>
        <p><a href="/api/logout.php">Logout</a></p>
    </div>
</body>
</html>
