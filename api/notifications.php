<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

require '../resources/db.php';

$userId = $_GET['user_id'] ?? null;

if ($userId) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($notifications);
} else {
    echo json_encode(["error" => "No user ID provided"]);
}
?>
