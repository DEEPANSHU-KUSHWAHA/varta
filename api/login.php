<?php
require '../resources/db.php';
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use OTPHP\TOTP;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $otp      = $_POST['otp'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $totp = TOTP::create($user['totp_secret']);
        if ($totp->verify($otp)) {
            $secret = getenv("JWT_SECRET");
            $payload = ["user_id" => $user['id'], "exp" => time() + 3600];
            $accessToken = JWT::encode($payload, $secret, 'HS256');

            $refreshToken = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+30 days"));

            $stmt = $conn->prepare("INSERT INTO sessions (user_id, token, refresh_token, refresh_expiry) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user['id'], $accessToken, $refreshToken, $expiry);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, 'New login detected', 'success')");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            


            echo json_encode([
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken,
            "refresh_expiry" => $expiry
            ]);

        } else {
            echo json_encode(["error" => "Invalid OTP"]);
        }
    } else {
        echo json_encode(["error" => "Invalid credentials"]);
    }
}
?>
