<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Varta</title>
    <!-- Styles -->
    <link rel="stylesheet" href="/public/css/navbar.css">
    <link rel="stylesheet" href="/public/css/sidebar.css">
    <link rel="stylesheet" href="/public/css/auth.css">
    <link rel="stylesheet" href="/public/css/home.css">
    <style>
        /* Spinner styles */
        #spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        #spinner img {
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            font-family: inherit;
        }
        .pagination .page-link {
            display: inline-block;
            padding: 6px 12px;
            background-color: #0077cc;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s ease;
            font-size: 14px;
        }
        .pagination .page-link:hover {
            background-color: #005fa3;
        }
        .pagination span {
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar local-bg" id="navbar">
        <div class="navbar-left">
            <?php include __DIR__ . '/logo.php'; ?>
            <?php include __DIR__ . '/pagination.php'; ?>
        </div>
        <div class="navbar-center" id="navbar-content" data-current="local">
            <ul class="nav-links">
                <li><a href="#" class="nav-option" data-page="home">Home</a></li>
                <li><a href="#" class="nav-option" data-page="auth">Login / Signup</a></li>
                <li><a href="#" class="nav-option" data-page="dashboard">Dashboard</a></li>
                <li><a href="#" class="nav-option" data-page="reset_password">Reset Password</a></li>
                <li><a href="#" class="nav-option" data-page="profile">Profile</a></li>
                <li><a href="#" class="nav-option" data-page="notifications">Notifications</a></li>
            </ul>
        </div>
        <div class="navbar-right">
            <?php include __DIR__ . '/user.php'; ?>
            <?php include __DIR__ . '/dropdown.php'; ?>
        </div>
    </nav>

    <!-- Main content -->
    <div id="content">
        <?php include __DIR__ . '/home.php'; ?>
    </div>

    <!-- Spinner -->
    <div id="spinner">
        <img src="/resources/logo/varta.png" alt="Loading...">
        <p>Loading...</p>
    </div>

    <!-- JS Router with Pagination + History -->
    <script>
    function showSpinner() {
        document.getElementById('spinner').style.display = 'block';
        document.getElementById('content').style.opacity = '0.3';
    }
    function hideSpinner() {
        document.getElementById('spinner').style.display = 'none';
        document.getElementById('content').style.opacity = '1';
    }

    // Generic loader with history support
    function loadPage(endpoint, page = 1, pushState = true) {
        showSpinner();
        fetch('/public/' + endpoint + '.php?page=' + page)
            .then(res => res.text())
            .then(html => {
                document.getElementById('content').innerHTML = html;
                hideSpinner();

                // Attach pagination handlers inside loaded content
                document.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', e => {
                        e.preventDefault();
                        const targetPage = link.getAttribute('data-page');
                        const targetEndpoint = link.getAttribute('data-endpoint');
                        loadPage(targetEndpoint, targetPage);
                    });
                });

                // Update browser history
                if (pushState) {
                    history.pushState({ endpoint: endpoint, page: page }, '', '#' + endpoint + '-p' + page);
                }
            })
            .catch(err => {
                console.error('Error loading page:', err);
                hideSpinner();
            });
    }

    // Attach navbar links
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-page]').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const endpoint = link.getAttribute('data-page');
                loadPage(endpoint, 1);
            });
        });

        // Handle back/forward buttons
        window.addEventListener('popstate', e => {
            if (e.state && e.state.endpoint) {
                loadPage(e.state.endpoint, e.state.page, false);
            } else {
                loadPage('home', 1, false);
            }
        });

        // Load initial page from hash if present
        if (window.location.hash) {
            const hash = window.location.hash.substring(1); // e.g. "notifications-p2"
            const [endpoint, pagePart] = hash.split('-p');
            const page = pagePart ? parseInt(pagePart, 10) : 1;
            loadPage(endpoint, page, false);
        }
    });

    // Glow logic (simplified)
    function setActiveGlow(view) {
        const navbar = document.getElementById('navbar');
        if (view === 'local') {
            navbar.classList.remove('global-bg');
            navbar.classList.add('local-bg');
        } else {
            navbar.classList.remove('local-bg');
            navbar.classList.add('global-bg');
        }
    }
    setActiveGlow('local');
    </script>
</body>
</html>
