<?php
require '../resources/db.php';
require '../vendor/autoload.php';
use OTPHP\TOTP;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $secret   = \ParagonIE\ConstantTime\Base32::encodeUpper(random_bytes(10));

    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, totp_secret) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $secret);
    $stmt->execute();

    echo json_encode(["message" => "Signup successful", "totp_secret" => $secret]);
}
?>
