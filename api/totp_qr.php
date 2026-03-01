<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;


require_once __DIR__ . '/../vendor/autoload.php';
$ga = new PHPGangsta_GoogleAuthenticator();

$secret = $ga->createSecret();
session_start();
$_SESSION['totp_secret'] = $secret;

$websiteName = "Varta";
$userEmail   = "newuser@varta.local";
$qrCodeUrl   = $ga->getQRCodeGoogleUrl($websiteName . ":" . $userEmail, $secret, $websiteName);

header('Content-Type: application/json');
echo json_encode([
    "qr" => $qrCodeUrl,
    "secret" => $secret
]);
