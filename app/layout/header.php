<?php
// app/layout/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Varta</title>
    <link rel="stylesheet" href="public/css/navbar.css">
    <link rel="stylesheet" href="public/css/sidebar.css">
</head>
<body>
    <!-- Logo always top-left -->
    <div class="logo">
        <img src="/resources/logo/varta.png" alt="Varta">
    </div>

    <!-- Navbar beside logo -->
    <div class="navbar">
        <div class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=sessions">Sessions</a>
            <a href="index.php?page=profile">Profile</a>
            <a href="index.php?page=dashboard">Dashboard</a>
            <a href="index.php?page=logout">Logout</a>
        </div>
    </div>

    <!-- Sidebar below logo -->
    <div class="sidebar">
        <ul>
            <li><a href="index.php?page=home">🏠 Home</a></li>
            <li><a href="index.php?page=sessions">📁 Sessions</a></li>
            <li><a href="index.php?page=profile">👤 Profile</a></li>
            <li><a href="index.php?page=dashboard">📊 Dashboard</a></li>
            <li><a href="index.php?page=logout">🔒 Logout</a></li>
        </ul>
    </div>
