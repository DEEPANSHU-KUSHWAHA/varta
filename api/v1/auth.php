<?php
/**
 * Authentication API Microservice
 * Handles login, logout, token refresh, user registration
 */

// Set JSON header and error handling immediately
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Catch any PHP errors and return as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]));
});

// Catch uncaught exceptions
set_exception_handler(function($e) {
    http_response_code(500);
    exit(json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]));
});

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../resources/db.php';
require_once __DIR__ . '/../../app/auth/jwt.php';
require_once __DIR__ . '/response.php';

$method = $_SERVER['REQUEST_METHOD'];
session_start();

if ($method !== 'POST') {
    exit(ApiResponse::error('Method not allowed', 405));
}

$action = $_GET['action'] ?? 'login';
$input = getJsonInput();

global $conn;

function logError($action, $data) {
    $logFile = __DIR__ . '/../../uploads/auth_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] Action: $action\n";
    $logEntry .= "Data: " . json_encode($data) . "\n";
    $logEntry .= "---\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
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

    $update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $update->bind_param("i", $user['id']);
    $update->execute();

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
    logError('register_attempt', $input);

    $username = sanitize($input['username'] ?? '');
    $email = sanitize($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $confirmPass = $input['confirm_password'] ?? '';
    $firstName = sanitize($input['first_name'] ?? '');

    $errors = [];
    if (empty($username)) $errors[] = 'username is required';
    if (empty($email)) $errors[] = 'email is required';
    if (empty($password)) $errors[] = 'password is required';
    if (empty($firstName)) $errors[] = 'first_name is required';
    if (!empty($password) && $password !== $confirmPass) $errors[] = 'passwords do not match';

    if (!empty($errors)) {
        logError('register_validation_failed', $errors);
        exit(ApiResponse::error('Validation failed: ' . implode(', ', $errors), 400, $errors));
    }

    try {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        if (!$check) {
            logError('register_prepare_error', ['error' => $conn->error]);
            exit(ApiResponse::error('Database error', 500));
        }

        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            logError('register_duplicate', ['username' => $username, 'email' => $email]);
            exit(ApiResponse::error('Username or email already exists', 409));
        }
    } catch (Exception $e) {
        logError('register_check_error', ['exception' => $e->getMessage()]);
        exit(ApiResponse::error('Database error: ' . $e->getMessage(), 500));
    }

    $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

    try {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $secret = $ga->createSecret();
        $encryptionKey = hex2bin(getenv('TOTP_ENC_KEY') ?: 'default256bitkey1234567890123456');
        $iv = openssl_random_pseudo_bytes(16);
        $encryptedSecret = $iv . openssl_encrypt($secret, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

        $insert = $conn->prepare("INSERT INTO users (username, email, password_hash, first_name, totp_secret_enc) VALUES (?, ?, ?, ?, ?)");
        if (!$insert) {
            logError('register_insert_prepare_error', ['error' => $conn->error]);
            exit(ApiResponse::error('Database error', 500));
        }

        $insert->bind_param("sssss", $username, $email, $passwordHash, $firstName, $encryptedSecret);
        if (!$insert->execute()) {
            logError('register_insert_execute_error', ['error' => $insert->error]);
            exit(ApiResponse::error('Registration failed: ' . $insert->error, 500));
        }

        $qrCodeUrl = $ga->getQRCodeGoogleUrl('Varta:' . $email, $secret, 'Varta');
        logError('register_success', ['user_id' => $insert->insert_id, 'username' => $username]);

        exit(ApiResponse::success([
            'user_id' => $insert->insert_id,
            'qr_code' => $qrCodeUrl,
            'secret' => $secret,
            'message' => 'Scan the QR code to enable 2FA'
        ], 'Registration successful', 201));
    } catch (Exception $e) {
        logError('register_exception', ['exception' => $e->getMessage()]);
        exit(ApiResponse::error('Server error: ' . $e->getMessage(), 500));
    }
}

function handleVerifyOtp($input, $conn) {
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
        exit(ApiResponse::error('Unknown action: ' . $action, 400));
}

