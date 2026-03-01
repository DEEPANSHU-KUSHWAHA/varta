<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    set_flash("You must be logged in to update your profile.", "error");
    header("Location: /public/auth.php");
    exit;
}

$userId = $_SESSION['user_id'];
$email  = trim($_POST['email'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$role   = trim($_POST['role'] ?? 'user');

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash("Invalid email format.", "error");
    header("Location: /public/dashboard.php");
    exit;
}

$avatarPath = null;
if (!empty($_FILES['avatar']['name'])) {
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $fileName = uniqid() . "_" . basename($_FILES['avatar']['name']);
    $targetFile = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
        $avatarPath = '/uploads/avatars/' . $fileName;
    } else {
        set_flash("Failed to upload avatar.", "error");
        header("Location: /public/dashboard.php");
        exit;
    }
}

$query = "UPDATE users SET email = ?, phone = ?, role = ?";
$params = [$email, $phone, $role];
$types  = "sss";

if ($avatarPath !== null) {
    $query .= ", avatar_path = ?";
    $params[] = $avatarPath;
    $types   .= "s";
}

$query .= ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
$params[] = $userId;
$types   .= "i";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();

set_flash("Profile updated successfully!", "success");
header("Location: /public/dashboard.php");
exit;
