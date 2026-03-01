<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';

session_start();

$username    = $_POST['username'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';
$otp         = $_POST['totp'] ?? '';

if ($newPassword !== $confirmPass) {
    die("Passwords do not match.");
}

$stmt = $conn->prepare("SELECT id, totp_secret_enc FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Decrypt secret
$totpSecretEnc = $user['totp_secret_enc'];
$encryptionKey = hex2bin(getenv('TOTP_ENC_KEY'));
$iv = substr($totpSecretEnc, 0, 16);
$ciphertext = substr($totpSecretEnc, 16);
$secret = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

$ga = new PHPGangsta_GoogleAuthenticator();
if (!$ga->verifyCode($secret, $otp, 2)) {
    die("Invalid OTP. Password reset denied.");
}

// Update password
$newHash = password_hash($newPassword, PASSWORD_ARGON2ID);
$update = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
$update->bind_param("si", $newHash, $user['id']);
$update->execute();

echo "Password reset successful!";
