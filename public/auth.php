<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
/** @var mysqli $conn */
global $conn;
require_once __DIR__ . '/../resources/flash.php';
?>

<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-tabs">
            <!-- Tab headers -->
            <ul class="tab-header">
                <li class="tab-link active" data-tab="login">Login</li>
                <li class="tab-link" data-tab="signup">Sign Up</li>
            </ul>

            <!-- Login Tab -->
            <div id="login" class="tab-content active">
                <h2>Login</h2>
                <?php show_flash(); ?>
                <form id="login-form" method="POST" action="/api/login.php">
                    <div class="form-group">
                        <label for="login_username">Username</label>
                        <input type="text" id="login_username" name="username" required autocomplete="username" placeholder="Username or email">
                    </div>

                    <div class="form-group">
                        <label for="login_password">Password</label>
                        <input type="password" id="login_password" name="password" required autocomplete="current-password" placeholder="Password">
                    </div>

                    <div class="form-group">
                        <label for="login_otp">One-Time Password (OTP)</label>
                        <input type="text" id="login_otp" name="totp" required autocomplete="one-time-code" placeholder="6‑digit code">
                    </div>

                    <!-- Remember Me -->
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember Me</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                <p><a href="#" class="nav-option" data-page="reset_password">Forgot your password?</a></p>
            </div>

            <!-- Signup Tab -->
            <div id="signup" class="tab-content">
                <h2>Sign Up</h2>
                <form id="signup-form" method="POST" action="/api/signup.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required placeholder="First name">
                    </div>

                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" placeholder="Middle name">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Last name">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" placeholder="Phone number">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Email address">
                    </div>

                    <div class="form-group">
                        <label for="signup_username">Username</label>
                        <input type="text" id="signup_username" name="username" required autocomplete="username" placeholder="Choose a username">
                    </div>

                    <div class="form-group">
                        <label for="signup_password">Password</label>
                        <input type="password" id="signup_password" name="password" required autocomplete="new-password" placeholder="Create a password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" placeholder="Confirm password">
                    </div>

                    <div class="form-group">
                        <label for="avatar">Avatar</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="user">User</option>
                            <option value="moderator">Moderator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>


                    <button type="submit" class="btn btn-primary">Sign Up</button>
                </form>
                <p>Already have an account? 
                    <a href="#" class="tab-link" data-tab="login">Login here</a>
                </p>

                <!-- QR display section for signup (hidden initially) -->
                <div id="signup-qr-section" style="display:none; text-align:center; margin-top:1rem;">
                    <p>Scan the QR code with your authenticator app:</p>
                    <img id="signup-qr-image" src="" alt="TOTP QR Code" style="max-width:180px; margin:10px auto; display:block;" />
                    <p style="color:#aaa; font-size:0.9rem;">Secret: <span id="signup-qr-secret"></span></p>
                    <div class="form-group" style="margin-top:1rem;">
                        <label for="signup-verify-code">Enter Code</label>
                        <input type="text" id="signup-verify-code" maxlength="6" inputmode="numeric" placeholder="000000" style="text-align:center; font-size:18px; letter-spacing:4px;" />
                    </div>
                    <button id="signup-verify-btn" class="btn btn-primary">Verify & Complete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Switcher Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-header .tab-link');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', (event) => {
            event.preventDefault();
            const target = tab.getAttribute('data-tab');

            // Remove active from all
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => {
                c.classList.remove('active');
                c.classList.remove('fade-in');
            });

            // Add active to selected with animation
            tab.classList.add('active');
            const targetContent = document.getElementById(target);
            if (targetContent) {
                targetContent.classList.add('active', 'fade-in');
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // === LOGIN AJAX ===
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('login_username').value;
            const password = document.getElementById('login_password').value;
            const totp = document.getElementById('login_otp').value;
            if (!username || !password || !totp) {
                alert('Please fill in all fields.');
                return;
            }
            try {
                const resp = await fetch('/api/v1/auth.php?action=login', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({username,password,totp})
                });
                const result = await resp.json();
                if (result.success) {
                    if (result.data && result.data.token) {
                        localStorage.setItem('token', result.data.token);
                    }
                    alert('Login successful');
                    window.location.href = '/dashboard.php';
                } else {
                    alert('Login failed: ' + result.message);
                }
            } catch (err) {
                console.error(err);
                alert('Error logging in');
            }
        });
    }

    // === SIGNUP AJAX + QR ===
    const signupForm = document.getElementById('signup-form');
    const qrSection = document.getElementById('signup-qr-section');
    const qrImage = document.getElementById('signup-qr-image');
    const qrSecret = document.getElementById('signup-qr-secret');
    let tempUserData = null;

    if (signupForm) {
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(signupForm);
            const data = {
                action: 'register',
                username: formData.get('username'),
                email: formData.get('email'),
                password: formData.get('password'),
                confirm_password: formData.get('confirm_password'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                phone: formData.get('phone')
            };
            try {
                const resp = await fetch('/api/v1/auth.php?action=register', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify(data)
                });
                const text = await resp.text();
                const result = JSON.parse(text);
                if (result.success) {
                    tempUserData = result.data;
                    if (qrImage && result.data.qr_code) qrImage.src = result.data.qr_code;
                    if (qrSecret && result.data.secret) qrSecret.textContent = result.data.secret;
                    signupForm.style.display = 'none';
                    if (qrSection) qrSection.style.display = 'block';
                } else {
                    alert('Registration failed: ' + result.message);
                }
            } catch (err) {
                console.error(err);
                alert('Error registering: ' + err.message);
            }
        });
    }

    document.getElementById('signup-verify-btn')?.addEventListener('click', async () => {
        const code = document.getElementById('signup-verify-code')?.value;
        if (!code || code.length !== 6) {
            alert('Please enter a valid 6-digit code');
            return;
        }
        try {
            const resp = await fetch('/api/v1/auth.php?action=verify-otp', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ user_id: tempUserData?.user_id, code })
            });
            const result = await resp.json();
            if (result.success) {
                alert('Account verified! Logging you in...');
                if (result.data && result.data.token) {
                    localStorage.setItem('token', result.data.token);
                }
                window.location.href = '/dashboard.php';
            } else {
                alert('Verification failed: ' + result.message);
            }
        } catch (err) {
            console.error(err);
            alert('Verification error: ' + err.message);
        }
    });
});
</script>
