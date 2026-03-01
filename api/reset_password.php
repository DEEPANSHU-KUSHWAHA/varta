<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPGangsta_GoogleAuthenticator;

session_start();
require_once __DIR__ . '/../config/db.php'; // your PDO connection

$username    = $_POST['username'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';
$otp         = $_POST['totp'] ?? '';

// Validate new password
if ($newPassword !== $confirmPass) {
    die("Passwords do not match.");
}

// Fetch user record
$stmt = $pdo->prepare("SELECT id, password_hash, totp_secret_enc FROM users WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Decrypt TOTP secret
$totpSecretEnc = $user['totp_secret_enc'];
$encryptionKey = hex2bin(getenv('TOTP_ENC_KEY')); // secure key from env/config
$iv = substr($totpSecretEnc, 0, 16);
$ciphertext = substr($totpSecretEnc, 16);
$secret = openssl_decrypt($ciphertext, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

if (!$secret) {
    die("TOTP secret decryption failed.");
}

// Verify OTP
$ga = new PHPGangsta_GoogleAuthenticator();
if (!$ga->verifyCode($secret, $otp, 2)) {
    die("Invalid OTP. Password reset denied.");
}

// Hash new password
$newHash = password_hash($newPassword, PASSWORD_ARGON2ID);

// Update password in DB
$update = $pdo->prepare("UPDATE users SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
$update->execute([
    ':hash' => $newHash,
    ':id'   => $user['id']
]);

echo "Password reset successful! You can now log in with your new password.";
