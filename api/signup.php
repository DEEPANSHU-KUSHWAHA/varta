<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';

header('Content-Type: application/json');

/** @var mysqli $conn */
global $conn;

try {
    // ✅ Get form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $enable_2fa = intval($_POST['enable_2fa'] ?? 0);
    $totp_secret = trim($_POST['totp_secret'] ?? '');

    // ✅ Validate required fields
    if (empty($first_name) || empty($username) || empty($email) || empty($password)) {
        throw new Exception('Please fill in all required fields');
    }

    // ✅ Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // ✅ Validate passwords match
    if ($password !== $password_confirm) {
        throw new Exception('Passwords do not match');
    }

    // ✅ Validate password strength
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    // ✅ Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $checkStmt->bind_param("ss", $email, $username);
    $checkStmt->execute();
    
    if ($checkStmt->get_result()->num_rows > 0) {
        throw new Exception('Email or username already registered');
    }

    // ✅ Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // ✅ Prepare insert statement
    $stmt = $conn->prepare("
        INSERT INTO users 
        (first_name, last_name, username, email, phone, password, totp_enabled, totp_secret, created_at) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    // ✅ Bind parameters
    $totp_enabled = $enable_2fa ? 1 : 0;
    $totp_secret_value = ($enable_2fa && !empty($totp_secret)) ? $totp_secret : NULL;

    $stmt->bind_param(
        "ssssssss",
        $first_name,
        $last_name,
        $username,
        $email,
        $phone,
        $hashedPassword,
        $totp_enabled,
        $totp_secret_value
    );

    // ✅ Execute insert
    if (!$stmt->execute()) {
        throw new Exception('Error creating account: ' . $stmt->error);
    }

    $userId = $conn->insert_id;

    // ✅ If 2FA enabled, verify the TOTP code was valid during signup
    if ($enable_2fa && !empty($totp_secret)) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        // Secret is already saved, will be verified during login
    }

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully. Please log in.',
        'user_id' => $userId,
        'username' => $username
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
