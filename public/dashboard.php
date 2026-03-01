<?php
session_start();
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

// Protect this page
if (!isset($_SESSION['user_id'])) {
    set_flash("You must be logged in to access the dashboard.", "error");
    header("Location: /public/auth.php");
    exit;
}

// Fetch user details
$stmt = $conn->prepare("SELECT username, email, phone, role, avatar_path, last_login 
                        FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Varta</title>
    <link rel="stylesheet" href="/resources/auth.css">
    <style>
        .profile-card {
            display: flex;
            align-items: center;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .profile-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        .profile-details {
            flex: 1;
        }
        .profile-details p {
            margin: 5px 0;
        }
        .edit-form {
            margin-top: 20px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }
        .edit-form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .edit-form input, .edit-form select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .edit-form button {
            margin-top: 15px;
            padding: 10px;
            width: 100%;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="auth-container">
    <?php show_flash(); ?>

    <h2>Welcome to your dashboard, <?php echo htmlspecialchars($user['username']); ?>!</h2>

    <?php if (!empty($user['last_login'])): ?>
        <p>Last login: <?php echo htmlspecialchars($user['last_login']); ?></p>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="profile-card">
        <?php if (!empty($user['avatar_path'])): ?>
            <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" alt="Avatar">
        <?php else: ?>
            <img src="/resources/default-avatar.png" alt="Default Avatar">
        <?php endif; ?>
        <div class="profile-details">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="edit-form">
        <h3>Edit Profile</h3>
        <form method="POST" action="/api/update_profile.php" enctype="multipart/form-data">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

            <label>Phone</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

            <label>Avatar</label>
            <input type="file" name="avatar" accept="image/*">

            <label>Role</label>
            <select name="role">
                <option value="user" <?php if($user['role']==='user') echo 'selected'; ?>>User</option>
                <option value="moderator" <?php if($user['role']==='moderator') echo 'selected'; ?>>Moderator</option>
                <option value="admin" <?php if($user['role']==='admin') echo 'selected'; ?>>Admin</option>
            </select>

            <button type="submit">Update Profile</button>
        </form>
    </div>

    <form method="POST" action="/api/logout.php" style="text-align:right; margin-top:20px;">
        <button type="submit">Logout</button>
    </form>
</div>
</body>
</html>
