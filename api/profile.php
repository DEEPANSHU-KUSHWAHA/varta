<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
require_once __DIR__ . '/../resources/flash.php';

header('Content-Type: application/json');
session_start();

/** @var mysqli $conn */
global $conn;

try {
    // ✅ Use session instead of POST for user_id (SECURITY FIX)
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    $userId = $_SESSION['user_id'];
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // ✅ Validate email format
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    $avatarPath = null;
    if (!empty($_FILES['avatar']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = uniqid() . "_" . basename($_FILES['avatar']['name']);
        $targetFile = $uploadDir . $fileName;
        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            throw new Exception('Failed to upload avatar');
        }
        $avatarPath = '/uploads/avatars/' . $fileName;
    }

    $query = "UPDATE users SET email = ?, phone = ?";
    $params = [$email, $phone];
    $types = "ss";

    if ($avatarPath) {
        $query .= ", avatar_path = ?";
        $params[] = $avatarPath;
        $types .= "s";
    }

    $query .= ", updated_at = NOW() WHERE id = ?";
    $params[] = $userId;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    http_response_code(200);
    echo json_encode(['message' => 'Profile updated successfully']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
}
?>
