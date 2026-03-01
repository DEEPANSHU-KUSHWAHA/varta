<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPGangsta_GoogleAuthenticator;

session_start();
require_once __DIR__ . '/../config/db.php'; // your PDO connection

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$otp      = $_POST['totp'] ?? '';

// Fetch user record
$stmt = $pdo->prepare("SELECT id, username, password_hash, totp_secret_enc FROM users WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Invalid username or password.");
}

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    die("Invalid username or password.");
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
    die("Invalid OTP.");
}

// Success: set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

echo "Login successful! Welcome, " . htmlspecialchars($user['username']);
