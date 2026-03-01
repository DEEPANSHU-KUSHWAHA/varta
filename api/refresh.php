<?php
require '../resources/db.php';
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
$secret = $secret ?? '';
/** @var mysqli $conn */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $refreshToken = $_POST['refresh_token'] ?? '';

    if ($refreshToken) {
        // Check if refresh token exists in DB
        $stmt = $conn->prepare("SELECT * FROM sessions WHERE refresh_token=?");
        $stmt->bind_param("s", $refreshToken);
        $stmt->execute();
        $session = $stmt->get_result()->fetch_assoc();

        if ($session) {
    $expiry = strtotime($session['refresh_expiry']);
    if ($expiry > time()) {
        // Issue new access token
        $payload = ["user_id" => $session['user_id'], "exp" => time() + 3600];
        $newAccessToken = JWT::encode($payload, $secret, 'HS256');

        // Extend refresh expiry by 30 days from now
        $newExpiry = date("Y-m-d H:i:s", strtotime("+30 days"));
        $stmt = $conn->prepare("UPDATE sessions SET token=?, refresh_expiry=? WHERE id=?");
        $stmt->bind_param("ssi", $newAccessToken, $newExpiry, $session['id']);
        $stmt->execute();

        echo json_encode([
            "access_token" => $newAccessToken,
            "refresh_expiry" => $newExpiry
        ]);
    } else {
        echo json_encode(["error" => "Refresh token expired"]);
    }
}

    } else {
        echo json_encode(["error" => "Refresh token required"]);
    }
}
?>
