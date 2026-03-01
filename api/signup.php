<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

session_start();

// Collect form data
$firstName   = trim($_POST['first_name'] ?? '');
$middleName  = trim($_POST['middle_name'] ?? '');
$lastName    = trim($_POST['last_name'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$username    = trim($_POST['username'] ?? '');
$password    = $_POST['password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';
$role        = $_POST['role'] ?? 'user';
$totpCode    = $_POST['totp'] ?? '';

// ✅ Mandatory field checks
if ($firstName === '' || $username === '' || $password === '') {
    set_flash("First name, username, and password are required.", "error");
    header("Location: /public/auth.php");
    exit;
}
if ($password !== $confirmPass) {
    set_flash("Passwords do not match.", "error");
    header("Location: /public/auth.php");
    exit;
}

// ✅ Username uniqueness check
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    set_flash("Username already exists. Please choose another.", "error");
    header("Location: /public/auth.php");
    exit;
}

// ✅ Handle avatar upload
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
    }
}

// ✅ Hash password securely
$passwordHash = password_hash($password, PASSWORD_ARGON2ID);

// ✅ Verify TOTP
$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $_SESSION['totp_secret'] ?? null;
if (!$secret || !$ga->verifyCode($secret, $totpCode, 2)) {
    set_flash("Invalid OTP.", "error");
    header("Location: /public/auth.php");
    exit;
}

// ✅ Encrypt TOTP secret
$encryptionKey = hex2bin(getenv('TOTP_ENC_KEY'));
$iv = random_bytes(16);
$encryptedSecret = openssl_encrypt($secret, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
$totpSecretEnc = $iv . $encryptedSecret;

// ✅ Insert user into DB
$stmt = $conn->prepare("INSERT INTO users 
    (username, email, phone, first_name, middle_name, last_name, avatar_path, password_hash, role, totp_secret_enc, last_login) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param(
    "sssssssssb",
    $username, $email, $phone, $firstName, $middleName, $lastName,
    $avatarPath, $passwordHash, $role, $totpSecretEnc
);
$stmt->send_long_data(9, $totpSecretEnc);
$stmt->execute();

unset($_SESSION['totp_secret']);

// Auto-login new user
$_SESSION['user_id'] = $conn->insert_id;
$_SESSION['username'] = $username;

set_flash("Account created successfully!", "success");
header("Location: /public/dashboard.php");
exit;
