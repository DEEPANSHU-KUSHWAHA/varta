<?php
require '../resources/db.php';
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$token = $_GET['token'] ?? '';

if ($token) {
    try {
        $secret = getenv("JWT_SECRET");
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        $userId = $decoded->user_id;

        $stmt = $conn->prepare("SELECT role FROM users WHERE id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $role = $stmt->get_result()->fetch_assoc()['role'];

        echo json_encode(["role" => $role]);
    } catch (Exception $e) {
        echo json_encode(["error" => "Invalid token"]);
    }
} else {
    echo json_encode(["error" => "No token provided"]);
}
?>
