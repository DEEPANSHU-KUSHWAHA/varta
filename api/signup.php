<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php'; // defines $conn

session_start();

$firstName   = $_POST['first_name'] ?? '';
$middleName  = $_POST['middle_name'] ?? null;
$lastName    = $_POST['last_name'] ?? null;
$phone       = $_POST['phone'] ?? null;
$email       = $_POST['email'] ?? '';
$username    = $_POST['username'] ?? '';
$password    = $_POST['password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';
$role        = $_POST['role'] ?? 'user';
$totpCode    = $_POST['totp'] ?? '';

if ($password !== $confirmPass) {
    die("Passwords do not match.");
}

// Avatar upload
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

// Hash password
$passwordHash = password_hash($password, PASSWORD_ARGON2ID);

// Verify TOTP
$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $_SESSION['totp_secret'] ?? null;
if (!$secret || !$ga->verifyCode($secret, $totpCode, 2)) {
    die("Invalid OTP.");
}

// Encrypt secret
$encryptionKey = hex2bin(getenv('TOTP_ENC_KEY'));
$iv = random_bytes(16);
$encryptedSecret = openssl_encrypt($secret, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
$totpSecretEnc = $iv . $encryptedSecret;

// Insert user
$stmt = $conn->prepare("INSERT INTO users 
    (username, email, phone, first_name, middle_name, last_name, avatar_path, password_hash, role, totp_secret_enc) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param(
    "sssssssssb",
    $username, $email, $phone, $firstName, $middleName, $lastName,
    $avatarPath, $passwordHash, $role, $totpSecretEnc
);
$stmt->send_long_data(9, $totpSecretEnc); // for VARBINARY
$stmt->execute();

unset($_SESSION['totp_secret']);
echo "Account created successfully!";
