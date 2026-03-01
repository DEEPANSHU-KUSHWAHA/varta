<?php
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;

require_once __DIR__ . '/../resources/db.php';

header('Content-Type: application/json');

$username = trim($_GET['username'] ?? '');
$response = ["exists" => false, "message" => "", "suggestions" => []];

if ($username === '') {
    echo json_encode(["exists" => false, "message" => "Enter a username"]);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response["exists"] = true;
    $response["message"] = "Username taken. Try another.";

    $base = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($username));
    $response["suggestions"] = [
        $base . rand(10,99),
        $base . "_" . rand(100,999),
        $base . date("Y"),
        $base . "_user",
        $base . rand(1000,9999)
    ];
} else {
    $response["exists"] = false;
    $response["message"] = "Username available!";
}

echo json_encode($response);
