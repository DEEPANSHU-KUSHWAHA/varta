<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

require '../resources/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $avatarFile = null;
    if (!empty($_FILES['avatar']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $avatarFile = time() . "_" . basename($_FILES["avatar"]["name"]);
        $targetFile = $targetDir . $avatarFile;
        move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile);
    }

    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        if ($avatarFile) {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password_hash=?, avatar=? WHERE id=?");
            $stmt->bind_param("ssssi", $username, $email, $passwordHash, $avatarFile, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password_hash=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $email, $passwordHash, $userId);
        }
    } else {
        if ($avatarFile) {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, avatar=? WHERE id=?");
            $stmt->bind_param("sssi", $username, $email, $avatarFile, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $username, $email, $userId);
        }
    }
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, 'Profile updated successfully', 'info')");
    $stmt->bind_param("i", $userId);
    $stmt->execute();


    echo json_encode(["message" => "Profile updated successfully"]);
}
?>
