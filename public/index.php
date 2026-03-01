<?php
session_start();

// If user is not logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: /api/login.php");
    exit;
}

// Otherwise, show dashboard
require __DIR__ . '/../app/sidebar/dashboard.php';
