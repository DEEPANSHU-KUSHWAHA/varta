<?php
session_start();

$page = $_GET['page'] ?? 'home';

// Redirect unauthenticated users to login/signup/home only
if (!isset($_SESSION['user_id']) && !in_array($page, ['login', 'signup', 'home'])) {
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
    <link rel="stylesheet" href="public/css/sidebar.css">
    <link rel="stylesheet" href="public/css/auth.css">
    <link rel="stylesheet" href="public/css/home.css">
</head>
<body>
    <!-- Logo always top-left -->
    <div class="logo">
        <img src="/resources/logo/varta.png" alt="Varta">
    </div>

    <!-- Navbar beside logo -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="navbar">
            <div class="nav-links">
                <a href="index.php?page=home">Home</a>
                <a href="index.php?page=sessions">Sessions</a>
                <a href="index.php?page=profile">Profile</a>
                <a href="index.php?page=dashboard">Dashboard</a>
                <a href="index.php?page=logout">Logout</a>
            </div>
            <!-- Hamburger menu for mobile -->
            <div class="hamburger" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <!-- Sidebar toggle button (mobile only) -->
        <div class="sidebar-toggle" onclick="toggleSidebar()">☰ Menu</div>

        <!-- Sidebar -->
        <div class="sidebar">
            <ul>
                <li><a href="index.php?page=home">🏠 Home</a></li>
                <li><a href="index.php?page=sessions">📁 Sessions</a></li>
                <li><a href="index.php?page=profile">👤 Profile</a></li>
                <li><a href="index.php?page=dashboard">📊 Dashboard</a></li>
                <li><a href="index.php?page=logout">🔒 Logout</a></li>
            </ul>
        </div>

        <!-- Overlay for mobile sidebar -->
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <?php endif; ?>

    <!-- Main content -->
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

    <!-- JS toggles -->
    <script>
    function toggleMenu() {
        document.querySelector('.nav-links').classList.toggle('active');
    }
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
        document.querySelector('.sidebar-overlay').classList.toggle('active');
    }
    </script>
</body>
</html>
