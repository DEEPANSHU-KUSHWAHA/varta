<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function createJWT($userId) {
    $secret = getenv("JWT_SECRET");
    $payload = [
        "user_id" => $userId,
        "exp" => time() + 3600
    ];
    return JWT::encode($payload, $secret, 'HS256');
}

function verifyJWT($token) {
    try {
        $secret = getenv("JWT_SECRET");
        return JWT::decode($token, new Key($secret, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}
?>
