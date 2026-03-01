<?php
require __DIR__ . '/../resources/db.php';
require __DIR__ . '/../vendor/autoload.php';

use OTPHP\TOTP;

/** @var mysqli $conn */
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        echo json_encode(["error" => "Username already taken"]);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Generate TOTP secret for 2FA
    $totp = TOTP::create();
    $totpSecret = $totp->getSecret();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, totp_secret) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $passwordHash, $totpSecret);
    $stmt->execute();

    $userId = $stmt->insert_id;

    // Create notification
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, 'Account created successfully', 'success')");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Set session and redirect
    $_SESSION['user_id'] = $userId;
    header("Location: /");
    exit;
}
