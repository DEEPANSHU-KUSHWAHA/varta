<?php
session_start();

// If user is not logged in and not already on login/signup, force them to login
$page = $_GET['page'] ?? 'home';
if (!isset($_SESSION['user_id']) && !in_array($page, ['login', 'signup'])) {
    header("Location: index.php?page=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Varta</title>
    <link rel="stylesheet" href="public/css/navbar.css">
</head>
<body>
    <?php include __DIR__ . '/app/navbar/index.php'; ?>
    <?php include __DIR__ . '/app/sidebar/index.php'; ?>

    <div id="content">
        <?php
        switch ($page) {
            case 'login':
                include __DIR__ . '/public/login.php';
                break;
            case 'signup':
                include __DIR__ . '/public/signup.php';
                break;
            case 'sessions':
                include __DIR__ . '/public/sessions.php';
                break;
            case 'profile': 
                include __DIR__ . '/app/sidebar/profile.php'; 
                break;
            case 'dashboard': 
                include __DIR__ . '/app/sidebar/dashboard.php'; 
                break;
            case 'notify': 
                include __DIR__ . '/public/notify.php'; 
                break;
            case 'notify_all': 
                include __DIR__ . '/public/notify_all.php'; 
                break;
            case 'logout':
                include __DIR__ . '/public/logout.php';
                break;
            case 'cleanup':
                include __DIR__ . '/api/cleanup.php';
                break;
            default:
                include __DIR__ . '/public/home.php';
        }
        ?>
    </div>
</body>
</html>
