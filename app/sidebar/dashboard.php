<?php
require __DIR__ . '/../../resources/db.php';
require __DIR__ . '/../../app/auth/jwt.php';

$token = $_GET['token'] ?? '';
$decoded = verifyJWT($token);

if (!$decoded) {
    die("Unauthorized");
}

$userId = $decoded->user_id;

// Get user info
$stmt = $conn->prepare("SELECT username, email, avatar, created_at FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Count active sessions
$result = $conn->query("SELECT COUNT(*) as count FROM sessions WHERE user_id=$userId");
$activeSessions = $result->fetch_assoc()['count'];

// Get last login
$result = $conn->query("SELECT MAX(created_at) as last_login FROM sessions WHERE user_id=$userId");
$lastLogin = $result->fetch_assoc()['last_login'];
?>

<div class="dashboard">
    <h2>Dashboard</h2>
    <div class="avatar">
        <?php if (!empty($user['avatar'])): ?>
            <img src="../../uploads/<?= $user['avatar'] ?>" alt="Avatar" width="100">
        <?php else: ?>
            <p>No avatar uploaded</p>
        <?php endif; ?>
    </div>
    <p><strong>Username:</strong> <?= $user['username'] ?></p>
    <p><strong>Email:</strong> <?= $user['email'] ?></p>
    <p><strong>Account Created:</strong> <?= $user['created_at'] ?></p>
    <p><strong>Active Sessions:</strong> <?= $activeSessions ?></p>
    <p><strong>Last Login:</strong> <?= $lastLogin ?></p>

    <div class="notifications">
        <h3>Recent Notifications</h3>
        <ul id="notifList"></ul>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadNotifications() {
    $.ajax({
        url: "../api/notifications.php?user_id=<?= $userId ?>",
        type: "GET",
        success: function(response) {
            let notifs = JSON.parse(response);
            let html = "";
            notifs.forEach(n => {
                html += "<li class='" + n.type + "'>" + n.message + " (" + n.created_at + ")</li>";
            });
            $("#notifList").html(html);
        }
    });
}

// Initial load
loadNotifications();

// Poll every 10 seconds
setInterval(loadNotifications, 10000);
</script>
