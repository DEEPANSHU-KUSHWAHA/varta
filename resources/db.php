<?php
require '../app/auth/jwt.php';
require '../resources/db.php';

$token = $_GET['token'] ?? '';
$decoded = verifyJWT($token);

if (!$decoded) {
    die("Unauthorized");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - Varta</title>
    <link rel="stylesheet" href="public/css/navbar.css">
</head>
<body>
    <?php include __DIR__ . '/../app/navbar/index.php'; ?>
    <h2>Welcome to Varta</h2>
    <p>Hello User #<?php echo $decoded->user_id; ?>, you are logged in.</p>
</body>
</html>
