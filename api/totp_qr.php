<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;

require_once __DIR__ . '/vendor/autoload.php'; // include composer autoload

use PHPGangsta_GoogleAuthenticator;

// Initialize Google Authenticator
$ga = new PHPGangsta_GoogleAuthenticator();

// Generate a new secret for the user
$secret = $ga->createSecret();

// Store secret temporarily in session until signup completes
session_start();
$_SESSION['totp_secret'] = $secret;

// Create QR code URL (for Google Authenticator, Authy, etc.)
$websiteName = "Varta";
$userEmail   = $_SESSION['pending_email'] ?? "newuser@varta.local";
$qrCodeUrl   = $ga->getQRCodeGoogleUrl($websiteName . ":" . $userEmail, $secret, $websiteName);

// Output QR code image
header('Content-Type: text/html');
echo "<img src='" . htmlspecialchars($qrCodeUrl) . "' alt='Scan this QR with Authenticator'>";
echo "<p>Secret Key: <strong>" . htmlspecialchars($secret) . "</strong></p>";
