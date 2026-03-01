<?php
require '../resources/db.php';
/** @var mysqli $conn */
// Expire sessions older than 1 hour
$expiry = time() - 3600;

$stmt = $conn->prepare("DELETE FROM sessions WHERE UNIX_TIMESTAMP(created_at) < ?");
$stmt->bind_param("i", $expiry);
$stmt->execute();

echo json_encode(["message" => "Expired sessions cleaned"]);
?>
