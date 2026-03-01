<?php
require '../resources/db.php';
/** @var mysqli $conn */
// Get token from request
$token = $_POST['token'] ?? '';

if ($token) {
    // Delete current session
    $stmt = $conn->prepare("DELETE FROM sessions WHERE token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
}

// Also clean up expired sessions (older than 1 hour)
$expiry = time() - 3600;
$stmt = $conn->prepare("DELETE FROM sessions WHERE UNIX_TIMESTAMP(created_at) < ?");
$stmt->bind_param("i", $expiry);
$stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, 'User logged out', 'warning')");
$stmt->bind_param("i", $userId);
$stmt->execute();


echo json_encode(["message" => "Logged out and expired sessions cleaned"]);
?>
