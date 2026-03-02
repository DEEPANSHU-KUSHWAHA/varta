<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
require_once __DIR__ . '/../resources/pagination.php';
/** @var mysqli $conn */
global $conn;

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("SELECT id, message, created_at FROM notifications ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

$countRes = $conn->query("SELECT COUNT(*) AS total FROM notifications");
$total = $countRes->fetch_assoc()['total'];
$totalPages = (int) ceil($total / $limit); // cast to int
?>
<div class="auth-container">
    <h2>Notifications</h2>
    <ul>
        <?php foreach ($notifications as $n): ?>
            <li>
                <strong><?php echo htmlspecialchars($n['message']); ?></strong>
                <br><small><?php echo htmlspecialchars($n['created_at']); ?></small>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php render_pagination($page, $totalPages, 'notifications'); ?>
</div>
