<?php
// Legacy login endpoint – forwards request to v1 microservice
header('Content-Type: application/json');

// Build request payload using incoming POST data
$payload = [
    'username' => $_POST['email'] ?? $_POST['username'] ?? '',
    'password' => $_POST['password'] ?? '',
    'totp' => $_POST['totp_code'] ?? ''
];

// call internal API
$ch = curl_init();
$apiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
        . "://" . $_SERVER['HTTP_HOST'] . "/api/v1/auth.php?action=login";

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal request failed']);
} else {
    // simply echo what v1 responded
    echo $response;
}

