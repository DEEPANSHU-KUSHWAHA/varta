<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

session_start();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$otp      = $_POST['totp'] ?? '';

if ($username === '' || $password === '' || $otp === '') {
    set_flash("Username, password, and OTP are required.", "error");
    header("Location: /public/auth.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password_hash, totp_secret_enc FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password_hash'])) {
    set_flash("Invalid username or password.", "error");
    header("Location: /public/auth.php");
    exit;
}

// Decrypt secret
$totpSecretEnc = $user['totp_secret_enc'];
$encryptionKey = hex2bin(getenv('TOTP_ENC_KEY'));
$iv = substr($totpSecretEnc, 0, 16);
$ciphertext = substr($totpSecretEnc, 16);
$secret = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

if (!$secret) {
    set_flash("TOTP secret decryption failed.", "error");
    header("Location: /public/auth.php");
    exit;
}

$ga = new PHPGangsta_GoogleAuthenticator();
if (!$ga->verifyCode($secret, $otp, 2)) {
    set_flash("Invalid OTP.", "error");
    header("Location: /public/auth.php");
    exit;
}

// Success
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
set_flash("Login successful!", "success");

header("Location: /public/dashboard.php");
exit;
