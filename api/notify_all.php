<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;

require '../resources/db.php';
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$token = $_POST['token'] ?? '';
$message = $_POST['message'] ?? '';
$type = $_POST['type'] ?? 'info';

if ($token && !empty($message)) {
    try {
        $secret = getenv("JWT_SECRET");
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        $userId = $decoded->user_id;

        // Check role
        $stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $role = $stmt->get_result()->fetch_assoc()['role'];

        if ($role !== 'admin') {
            echo json_encode(["error" => "Unauthorized: only admins can broadcast"]);
            exit;
        }

        // Broadcast to all users
        $users = $conn->query("SELECT id FROM users");
        while ($row = $users->fetch_assoc()) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $row['id'], $message, $type);
            $stmt->execute();
        }

        echo json_encode(["message" => "Broadcast notification sent to all users"]);
    } catch (Exception $e) {
        echo json_encode(["error" => "Invalid token"]);
    }
} else {
    echo json_encode(["error" => "Missing token or message"]);
}
?>
