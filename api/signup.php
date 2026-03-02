<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
require_once __DIR__ . '/../resources/flash.php';

header('Content-Type: application/json');
session_start();

/** @var mysqli $conn */
global $conn;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? null;

    // STEP 1: Generate QR Code and Secret Key
    if ($action === 'generate-qr') {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        if (empty($email)) {
            throw new Exception('Email is required');
        }

        // Check if email already exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            throw new Exception('Email already exists');
        }
        $stmt->close();

        // Generate secret
        $ga = new PHPGangsta_GoogleAuthenticator();
        $secret = $ga->createSecret();

        // Store in session for verification later
        $_SESSION['totp_secret'] = $secret;
        $_SESSION['signup_email'] = $email;

        // Generate QR code URL
        $qrCodeUrl = $ga->getQRCodeGoogleUrl('VartaApp', $secret);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'qr_code_url' => $qrCodeUrl,
            'secret_key' => $secret,
            'message' => 'QR code generated successfully'
        ]);
        exit;
    }

    // STEP 2: Verify TOTP and Create Account
    $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $middleName = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
    $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $totpCode = isset($_POST['totp']) ? trim($_POST['totp']) : '';
    $role = 'user';

    // Validate input
    if (empty($firstName) || empty($username) || empty($password) || empty($email)) {
        throw new Exception('Required fields are missing');
    }

    if ($password !== $confirmPassword) {
        throw new Exception('Passwords do not match');
    }

    if (empty($totpCode) || strlen($totpCode) !== 6 || !ctype_digit($totpCode)) {
        throw new Exception('Invalid OTP code');
    }

    // Check if username already exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Username already exists');
    }
    $stmt->close();

    // Check if email already exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Email already exists');
    }
    $stmt->close();

    // Verify TOTP code
    if (!isset($_SESSION['totp_secret'])) {
        throw new Exception('Session expired. Please restart signup.');
    }

    $ga = new PHPGangsta_GoogleAuthenticator();
    $totpSecret = $_SESSION['totp_secret'];

    // Verify the code (2 = 1 code window - 30 seconds grace period)
    if (!$ga->verifyCode($totpSecret, $totpCode, 2)) {
        throw new Exception('Invalid OTP code. Please check the code and try again.');
    }

    // Hash password using ARGON2ID
    $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

    // Encrypt TOTP secret using AES-256-CBC
    $encryptionKey = hex2bin(getenv('TOTP_ENC_KEY') ?:
        '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef');
    $iv = openssl_random_pseudo_bytes(16);
    $encryptedSecret = openssl_encrypt($totpSecret, 'AES-256-CBC', $encryptionKey, OPENSSL_RAW_DATA, $iv);
    $totpSecretEnc = bin2hex($iv . $encryptedSecret);

    // Handle avatar upload if provided
    $avatarPath = null;
    if (!empty($_FILES['avatar']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetFile)) {
            $avatarPath = '/uploads/avatars/' . $fileName;
        }
    }

    // Insert user into database
    $stmt = $conn->prepare('
        INSERT INTO users 
        (first_name, middle_name, last_name, email, username, password_hash, 
         totp_secret_enc, phone, avatar_path, role, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');

    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param(
        'sssssssss',
        $firstName,
        $middleName,
        $lastName,
        $email,
        $username,
        $passwordHash,
        $totpSecretEnc,
        $phone,
        $avatarPath,
        $role
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create user: ' . $stmt->error);
    }

    $userId = $stmt->insert_id;
    $stmt->close();

    // Clear session
    unset($_SESSION['totp_secret']);
    unset($_SESSION['signup_email']);

    // Set auto-login (optional - comment out if you want user to manually login)
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    set_flash('Account created successfully! 2FA is now enabled.', 'success');

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully',
        'user_id' => $userId,
        'redirect' => '/public/dashboard.php'
    ]);
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
