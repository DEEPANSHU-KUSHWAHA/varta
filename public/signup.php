<?php
// public/signup.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup - Varta</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
<div class="auth-container signup">
    <h2>Create Account</h2>
    <form method="POST" action="/api/signup.php" enctype="multipart/form-data">
        
        <!-- Avatar selection/upload -->
        <label for="avatar">Choose Avatar</label>
        <input type="file" id="avatar" name="avatar" accept="image/*">

        <!-- Names -->
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" required>

        <label for="middle_name">Middle Name (optional)</label>
        <input type="text" id="middle_name" name="middle_name">

        <label for="last_name">Last Name (optional)</label>
        <input type="text" id="last_name" name="last_name">

        <!-- Phone with country code -->
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required placeholder="+91 9876543210">

        <!-- Email -->
        <label for="email">Email (verified domain)</label>
        <input type="email" id="email" name="email" required autocomplete="email">

        <!-- Username (auto-suggest) -->
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required readonly value="auto-generated">

        <!-- Password -->
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="new-password">

        <label for="confirm_password">Re-enter Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">

        <!-- Role -->
        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="user">User</option>
            <option value="moderator">Moderator</option>
            <option value="admin">Admin</option>
        </select>

        <!-- 2FA Setup -->
        <p>Scan the QR code with your authenticator app to enable 2FA (TOTP).</p>
        <img src="/api/totp_qr.php" alt="TOTP QR Code">
        <label for="totp">Enter 6-digit OTP</label>
        <input type="text" id="totp" name="totp" required autocomplete="one-time-code">

        <button type="submit">Sign Up</button>
    </form>
</div>
</body>
</html>
