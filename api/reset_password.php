<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';

session_start();

$username    = trim($_POST['username'] ?? '');
$newPassword = $_POST['new_password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';
$otp         = $_POST['totp'] ?? '';

// ✅ Mandatory checks
if ($username === '' || $newPassword === '' || $confirmPass === '' || $otp === '') {
    set_flash("All fields are required.", "error");
    header("Location: /public/auth.php");
    exit;
}
if ($newPassword !== $confirmPass) {
    set_flash("Passwords do not match.", "error");
    header("Location: /public/auth.php");
    exit;
}

// ✅ Fetch user
$stmt = $conn->prepare("SELECT id, totp_secret_enc FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    set_flash("User not found.", "error");
    header("Location: /public/auth.php");
    exit;
}

// ✅ Decrypt TOTP secret
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

// ✅ Verify OTP
$ga = new PHPGangsta_GoogleAuthenticator();
if (!$ga->verifyCode($secret, $otp, 2)) {
    set_flash("Invalid OTP. Password reset denied.", "error");
    header("Location: /public/auth.php");
    exit;
}

// ✅ Hash new password
$newHash = password_hash($newPassword, PASSWORD_ARGON2ID);

// ✅ Update password
$update = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
$update->bind_param("si", $newHash, $user['id']);
$update->execute();

set_flash("Password reset successful!", "success");
header("Location: /public/auth.php");
exit;
