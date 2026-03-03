<?php
/**
 * Authentication API Microservice
 * Handles login, logout, token refresh, user registration
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../resources/db.php';
require_once __DIR__ . '/../../app/auth/jwt.php';
require_once __DIR__ . '/response.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

session_start();

// Only allow POST for auth endpoints
if ($method !== 'POST') {
    exit(ApiResponse::error('Method not allowed', 405));
}

$action = $_GET['action'] ?? 'login';
$input = getJsonInput();

global $conn;

switch ($action) {
    case 'login':
        handleLogin($input, $conn);
        break;
    case 'logout':
        handleLogout();
        break;
    case 'register':
        handleRegister($input, $conn);
        break;
    case 'verify-otp':
        handleVerifyOtp($input, $conn);
        break;
    case 'refresh-token':
        handleRefreshToken($input, $conn);
        break;
    default:
        exit(ApiResponse::error('Unknown action', 400));
}

function handleLogin($input, $conn) {
    $username = sanitize($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $otp = sanitize($input['totp'] ?? '');

    if (empty($username) || empty($password) || empty($otp)) {
        exit(ApiResponse::error('Missing required fields', 400));
    }

    $stmt = $conn->prepare("SELECT id, username, password_hash, totp_secret_enc FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        exit(ApiResponse::error('Invalid credentials', 401));
    }

    // Verify TOTP
    $totpSecretEnc = $user['totp_secret_enc'];
    $encryptionKey = hex2bin(getenv('TOTP_ENC_KEY') ?: 'default256bitkey1234567890123456');
    $iv = substr((string)$totpSecretEnc, 0, 16);
    $ciphertext = substr((string)$totpSecretEnc, 16);
    $secret = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

    if (!$secret) {
        exit(ApiResponse::error('TOTP verification failed', 401));
    }

    $ga = new PHPGangsta_GoogleAuthenticator();
    if (!$ga->verifyCode($secret, $otp, 2)) {
        exit(ApiResponse::error('Invalid OTP', 401));
    }

    // Update last login
    $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $update->bind_param("i", $user['id']);
    $update->execute();

    // Create JWT token
    $jwtToken = createJWTToken($user['id']);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];

    exit(ApiResponse::success([
        'user_id' => $user['id'],
        'username' => $user['username'],
        'token' => $jwtToken
    ], 'Login successful', 200));
}

function handleLogout() {
    session_destroy();
    exit(ApiResponse::success(null, 'Logged out successfully'));
}

function handleRegister($input, $conn) {
    $username = sanitize($input['username'] ?? '');
    $email = sanitize($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $confirmPass = $input['confirm_password'] ?? '';
    $firstName = sanitize($input['first_name'] ?? '');

    // Detailed validation
    $errors = [];
    if (empty($username)) $errors[] = 'username is required';
    if (empty($email)) $errors[] = 'email is required';
    if (empty($password)) $errors[] = 'password is required';
    if (empty($firstName)) $errors[] = 'first_name is required';
    if (!empty($password) && $password !== $confirmPass) $errors[] = 'passwords do not match';
    
    if (!empty($errors)) {
        exit(ApiResponse::error('Validation failed: ' . implode(', ', $errors), 400, $errors));
    }

    // Check if username exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        exit(ApiResponse::error('Username or email already exists', 409));
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

    // Generate TOTP secret and store encrypted
    $ga = new PHPGangsta_GoogleAuthenticator();
    $secret = $ga->createSecret();
    $encryptionKey = hex2bin(getenv('TOTP_ENC_KEY') ?: 'default256bitkey1234567890123456');
    $iv = openssl_random_pseudo_bytes(16);
    $encryptedSecret = $iv . openssl_encrypt($secret, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

    // Insert user
    $insert = $conn->prepare("INSERT INTO users (username, email, password_hash, first_name, totp_secret_enc) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("sssss", $username, $email, $passwordHash, $firstName, $encryptedSecret);

    if (!$insert->execute()) {
        exit(ApiResponse::error('Registration failed', 500));
    }

    $qrCodeUrl = $ga->getQRCodeGoogleUrl('Varta:' . $email, $secret, 'Varta');

    exit(ApiResponse::success([
        'user_id' => $insert->insert_id,
        'qr_code' => $qrCodeUrl,
        'secret' => $secret,
        'message' => 'Scan the QR code to enable 2FA'
    ], 'Registration successful', 201));
}

function handleVerifyOtp($input, $conn) {
    // allow either authenticated user or pass user_id after registration
    $userId = null;
    if (!empty($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    } elseif (!empty($input['user_id'])) {
        $userId = intval($input['user_id']);
    }

    if (!$userId) {
        exit(ApiResponse::error('Not authenticated', 401));
    }

    $otp = sanitize($input['totp'] ?? $input['code'] ?? '');

    if (empty($otp)) {
        exit(ApiResponse::error('OTP is required', 400));
    }

    $stmt = $conn->prepare("SELECT totp_secret_enc FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        exit(ApiResponse::error('User not found', 404));
    }

    $encryptionKey = hex2bin(getenv('TOTP_ENC_KEY') ?: 'default256bitkey1234567890123456');
    $iv = substr((string)$user['totp_secret_enc'], 0, 16);
    $ciphertext = substr((string)$user['totp_secret_enc'], 16);
    $secret = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

    $ga = new PHPGangsta_GoogleAuthenticator();
    if (!$ga->verifyCode($secret, $otp, 2)) {
        exit(ApiResponse::error('Invalid OTP', 401));
    }

    // generate token and log in
    $jwtToken = createJWTToken($userId);
    $_SESSION['user_id'] = $userId;
    
    exit(ApiResponse::success([
        'token' => $jwtToken,
        'user_id' => $userId
    ], 'OTP verified and user logged in'));
}

function handleRefreshToken($input, $conn) {
    $token = $input['token'] ?? '';
    if (empty($token)) {
        exit(ApiResponse::error('Token is required', 400));
    }

    try {
        $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
        $newToken = createJWTToken($decoded->user_id);
        exit(ApiResponse::success(['token' => $newToken], 'Token refreshed'));
    } catch (Exception $e) {
        exit(ApiResponse::error('Invalid token', 401));
    }
}
