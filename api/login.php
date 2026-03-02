<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';

header('Content-Type: application/json');

/** @var mysqli $conn */
global $conn;

try {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $totp_code = trim($_POST['totp_code'] ?? '');

    // ✅ Validate inputs
    if (empty($email) || empty($password)) {
        throw new Exception('Email and password are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // ✅ Find user by email
    $stmt = $conn->prepare("
        SELECT id, username, password, totp_enabled, totp_secret 
        FROM users 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Invalid email or password');
    }

    $user = $result->fetch_assoc();

    // ✅ Verify password
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Invalid email or password');
    }

    // ✅ Check if 2FA is enabled
    if ($user['totp_enabled']) {
        // If TOTP code not provided yet, ask for it
        if (empty($totp_code)) {
            http_response_code(200);
            echo json_encode([
                'success' => false,
                'totp_required' => true,
                'message' => 'Two-Factor Authentication required'
            ]);
            exit;
        }

        // ✅ Verify TOTP code
        require_once __DIR__ . '/../vendor/autoload.php';
        $ga = new PHPGangsta_GoogleAuthenticator();

        if (!$ga->verifyCode($user['totp_secret'], $totp_code, 2)) {
            throw new Exception('Invalid two-factor authentication code');
        }
    }

    // ✅ Set session and login user
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $email;

    // ✅ Update last login timestamp
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $user['id']);
    $updateStmt->execute();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user_id' => $user['id'],
        'username' => $user['username']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
