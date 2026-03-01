<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth - Varta</title>
    <link rel="stylesheet" href="/resources/auth.css">
</head>
<body>
<?php require_once __DIR__ . '/../resources/flash.php'; show_flash(); ?>
<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Show welcome + logout if logged in -->
    <div class="auth-container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <form method="POST" action="/api/logout.php" style="text-align:right;">
            <button type="submit">Logout</button>
        </form>
    </div>
<?php else: ?>
    <!-- Show login/signup forms if not logged in -->
    <div class="auth-container">
        <div class="auth-tabs">
            <button id="login-tab" class="active" onclick="showForm('login')">Login</button>
            <button id="signup-tab" onclick="showForm('signup')">Sign Up</button>
        </div>

        <!-- Login Form -->
        <form id="login-form" class="active form-panel" method="POST" action="/api/login.php">
            <h2>Login</h2>
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>OTP</label>
            <input type="text" name="totp" required>
            <button type="submit">Login</button>
        </form>

        <!-- Signup Form -->
        <form id="signup-form" class="form-panel" method="POST" action="/api/signup.php" enctype="multipart/form-data">
            <h2>Sign Up</h2>
            <label>First Name</label>
            <input type="text" name="first_name" required>
            <label>Middle Name (optional)</label>
            <input type="text" name="middle_name">
            <label>Last Name (optional)</label>
            <input type="text" name="last_name">

            <label>Phone (optional)</label>
            <input type="tel" name="phone">

            <label>Email</label>
            <input type="email" name="email">

            <label>Username</label>
            <input type="text" id="username" name="username" required>
            <div id="username-status"></div>

            <label>Password</label>
            <input type="password" name="password" required>
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>

            <label>Avatar</label>
            <input type="file" name="avatar" accept="image/*">

            <label>Role</label>
            <select name="role">
                <option value="user">User</option>
                <option value="moderator">Moderator</option>
                <option value="admin">Admin</option>
            </select>

            <!-- TOTP Setup -->
            <p>Scan this QR code with your authenticator app:</p>
            <img id="totp-qr" alt="TOTP QR Code">
            <p>Or enter this key manually: <span id="totp-key"></span></p>

            <label>Enter OTP</label>
            <input type="text" name="totp" required>

            <button type="submit">Sign Up</button>
        </form>
    </div>

    <script>
    function showForm(form) {
        document.getElementById('login-form').classList.remove('active');
        document.getElementById('signup-form').classList.remove('active');
        document.getElementById('login-tab').classList.remove('active');
        document.getElementById('signup-tab').classList.remove('active');

        if (form === 'login') {
            document.getElementById('login-form').classList.add('active');
            document.getElementById('login-tab').classList.add('active');
        } else {
            document.getElementById('signup-form').classList.add('active');
            document.getElementById('signup-tab').classList.add('active');
        }
    }

    // Username availability check
    document.getElementById('username').addEventListener('keyup', function() {
        let username = this.value;
        if (username.length > 2) {
            fetch('/api/check_username.php?username=' + encodeURIComponent(username))
                .then(res => res.json())
                .then(data => {
                    let status = document.getElementById('username-status');
                    status.innerHTML = data.message;

                    if (data.exists && data.suggestions.length > 0) {
                        let suggestionHTML = "<br>Suggestions: ";
                        data.suggestions.forEach(s => {
                            suggestionHTML += `<span class="suggestion" onclick="chooseUsername('${s}')">${s}</span> `;
                        });
                        status.innerHTML += suggestionHTML;
                    }
                });
        }
    });

    function chooseUsername(name) {
        document.getElementById('username').value = name;
        document.getElementById('username-status').textContent = "Selected username: " + name;
    }

    // Fetch QR + secret
    fetch('/api/totp_qr.php')
        .then(res => res.json())
        .then(data => {
            document.getElementById('totp-qr').src = data.qr;
            document.getElementById('totp-key').textContent = data.secret;
        });
    </script>
<?php endif; ?>
</body>
</html>
