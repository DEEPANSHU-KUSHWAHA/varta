<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPGangsta_GoogleAuthenticator;

session_start();
require_once __DIR__ . '/../config/db.php'; // your DB connection

// Collect form data
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

// Validate password match
if ($password !== $confirmPass) {
    die("Passwords do not match.");
}

// Handle avatar upload
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

// Hash password securely
$passwordHash = password_hash($password, PASSWORD_ARGON2ID);

// TOTP verification
$ga = new PHPGangsta_GoogleAuthenticator();
$secret = $_SESSION['totp_secret'] ?? null;

if (!$secret) {
    die("TOTP secret not found. Please restart signup.");
}

if (!$ga->verifyCode($secret, $totpCode, 2)) {
    die("Invalid OTP. Please try again.");
}

// Encrypt TOTP secret before saving
// Example using OpenSSL AES-256-CBC
$encryptionKey = hex2bin(getenv('TOTP_ENC_KEY')); // store securely in env/config
$iv = random_bytes(16);
$encryptedSecret = openssl_encrypt($secret, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
$totpSecretEnc = $iv . $encryptedSecret; // prepend IV for later decryption

// Insert into database
$stmt = $pdo->prepare("
    INSERT INTO users 
    (username, email, phone, first_name, middle_name, last_name, avatar_path, password_hash, role, totp_secret_enc) 
    VALUES (:username, :email, :phone, :first_name, :middle_name, :last_name, :avatar_path, :password_hash, :role, :totp_secret_enc)
");

$stmt->execute([
    ':username'        => $username,
    ':email'           => $email,
    ':phone'           => $phone,
    ':first_name'      => $firstName,
    ':middle_name'     => $middleName,
    ':last_name'       => $lastName,
    ':avatar_path'     => $avatarPath,
    ':password_hash'   => $passwordHash,
    ':role'            => $role,
    ':totp_secret_enc' => $totpSecretEnc
]);

// Clear temp secret
unset($_SESSION['totp_secret']);

echo "Account created successfully! You can now log in.";
