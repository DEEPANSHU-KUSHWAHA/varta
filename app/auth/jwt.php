<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Create JWT Token for user authentication
 * Token expires in 24 hours
 */
function createJWTToken($userId) {
    $secret = getenv('JWT_SECRET') ?: 'your-secret-key';
    $payload = [
        'user_id' => $userId,
        'exp' => time() + 86400, // 24 hours
        'iat' => time()
    ];
    return JWT::encode($payload, $secret, 'HS256');
}

/**
 * Verify and decode JWT token
 * Returns decoded token or null if invalid
 */
function verifyJWTToken($token) {
    try {
        $secret = getenv('JWT_SECRET') ?: 'your-secret-key';
        return JWT::decode($token, new Key($secret, 'HS256'));
    } catch (Exception $e) {
        return null;
    }
}

// Backward compatibility aliases
function createJWT($userId) {
    return createJWTToken($userId);
}

function verifyJWT($token) {
    return verifyJWTToken($token);
}
?>
