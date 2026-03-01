<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;

require '../resources/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $message = $_POST['message'] ?? '';
    $type = $_POST['type'] ?? 'info'; // default type

    if ($userId && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $message, $type);
        $stmt->execute();

        echo json_encode(["message" => "Notification sent successfully"]);
    } else {
        echo json_encode(["error" => "Missing user_id or message"]);
    }
}
?>
