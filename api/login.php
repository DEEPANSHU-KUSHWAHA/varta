<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';

session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$otp      = $_POST['totp'] ?? '';

$stmt = $conn->prepare("SELECT id, username, password_hash, totp_secret_enc FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password_hash'])) {
    die("Invalid username or password.");
}

// Decrypt TOTP secret
$totpSecretEnc = $user['totp_secret_enc'];
$encryptionKey = hex2bin(getenv('TOTP_ENC_KEY'));
$iv = substr($totpSecretEnc, 0, 16);
$ciphertext = substr($totpSecretEnc, 16);
$secret = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

$ga = new PHPGangsta_GoogleAuthenticator();
if (!$ga->verifyCode($secret, $otp, 2)) {
    die("Invalid OTP.");
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

echo "Login successful!";
