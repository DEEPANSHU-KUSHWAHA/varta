<?php
require __DIR__ . '/../../resources/db.php';
require __DIR__ . '/../../app/auth/jwt.php';

$token = $_GET['token'] ?? '';
$decoded = verifyJWT($token);

if (!$decoded) {
    die("Unauthorized");
}

$userId = $decoded->user_id;
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<h2>User Profile</h2>
<h2>User Profile</h2>
<form method="POST" action="../../api/profile.php" enctype="multipart/form-data">
    <input type="hidden" name="user_id" value="<?= $userId ?>">

    <label>Username:</label>
    <input type="text" name="username" value="<?= $user['username'] ?>"><br>

    <label>Email:</label>
    <input type="email" name="email" value="<?= $user['email'] ?>"><br>

    <label>New Password:</label>
    <input type="password" name="password"><br>

    <label>Avatar:</label>
    <input type="file" name="avatar"><br>

    <?php if (!empty($user['avatar'])): ?>
        <img src="../../uploads/<?= $user['avatar'] ?>" alt="Avatar" width="100">
    <?php endif; ?>

    <button type="submit">Update Profile</button>
</form>

