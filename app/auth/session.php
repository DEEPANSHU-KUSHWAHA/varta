<?php
require '../resources/db.php';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$result = $conn->query("SELECT * FROM sessions LIMIT $limit OFFSET $offset");
$sessions = $result->fetch_all(MYSQLI_ASSOC);

$total = $conn->query("SELECT COUNT(*) as count FROM sessions")->fetch_assoc()['count'];
$totalPages = ceil($total / $limit);

echo json_encode(["sessions"=>$sessions,"totalPages"=>$totalPages]);
?>
