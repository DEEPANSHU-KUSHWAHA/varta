<?php
/**
 * Varta - Signup Page
 * User registration with 2FA setup
 */
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
require_once __DIR__ . '/../resources/flash.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

/** @var mysqli $conn */
global $conn;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#075e54">
    <meta name="description" content="Varta - Create your account">
    <title>Sign Up - Varta Messaging</title>
    <link rel="stylesheet" href="/public/css/auth.css">
    <style>
        :root {
            --primary: #075e54;
            --primary-dark: #054a3f;
            --primary-light: #0a8566;
            --secondary: #25d366;
            --accent: #00d4ff;
            --danger: #f44336;
            --dark: #0f0f15;
            --light: #f0f0f0;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, var(--dark) 0%, var(--primary-dark) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .signup-container {
            width: 100%;
            max-width: 520px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 40px;
            max-height: 85vh;
            overflow-y: auto;
            backdrop-filter: blur(10px);
        }

        .signup-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .signup-header h1 {
            font-size: 32px;
            color: var(--primary);
            font-weight: 700;
            letter-spacing: -1px;
            margin: 0;
        }

        .signup-header p {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(7, 94, 84, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn {
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            margin-top: 10px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(7, 94, 84, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideInDown 0.3s ease-out;
        }

        .alert.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .password-strength {
            margin-top: 4px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            transition: var(--transition);
            width: 0%;
        }

        @media (max-width: 480px) {
            .signup-container {
                max-width: 100%;
                border-radius: 0;
                padding: 20px;
            }

            .signup-header h1 {
                font-size: 28px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1>🚀 Join Varta</h1>
            <p>Create your account to start messaging</p>
        </div>

        <?php show_flash(); ?>

        <!-- QR Code Display Section (Hidden by default) -->
        <div id="qr-section" style="display: none; text-align: center; margin-bottom: 20px;">
            <h3>Enable Two-Factor Authentication</h3>
            <p>Scan this QR code with your authenticator app:</p>
            <img id="qr-code" src="" alt="TOTP QR Code" style="max-width: 200px; margin: 20px 0;">
            <p>Can't scan? Enter manually: <code id="totp-secret" style="background: #f0f0f0; padding: 8px; display: inline-block;"></code></p>
            
            <div class="form-group">
                <label for="totp-verify">Enter the 6-digit code:</label>
                <input type="text" id="totp-verify" maxlength="6" placeholder="000000" style="text-align: center; font-size: 24px; letter-spacing: 5px;">
            </div>
            
            <button type="button" id="verify-totp-btn" class="btn btn-primary">Verify & Complete Registration</button>
        </div>

        <form method="POST" action="/api/v1/auth.php?action=register" id="signup-form" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input 
                        type="text" 
                        id="first_name" 
                        name="first_name" 
                        required 
                        placeholder="John"
                    />
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input 
                        type="text" 
                        id="last_name" 
                        name="last_name" 
                        required 
                        placeholder="Doe"
                    />
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="your@email.com"
                    autocomplete="email"
                />
            </div>

            <div class="form-group">
                <label for="username">Username *</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    placeholder="Choose a username"
                    autocomplete="username"
                />
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    placeholder="+1 (555) 123-4567"
                />
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="At least 8 characters"
                    autocomplete="new-password"
                    minlength="8"
                />
                <div class="password-strength">
                    <div class="password-strength-bar"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password *</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    required 
                    placeholder="Confirm your password"
                    autocomplete="new-password"
                />
            </div>

            <div class="form-group">
                <label for="avatar">Profile Picture</label>
                <input 
                    type="file" 
                    id="avatar" 
                    name="avatar" 
                    accept="image/*"
                />
                <small style="color: #999;">JPG, PNG or GIF. Max 5MB.</small>
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea 
                    id="bio" 
                    name="bio" 
                    placeholder="Tell us about yourself..."
                    maxlength="255"
                ></textarea>
                <small style="color: #999;">Max 255 characters</small>
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="/login-page.php">Log in here</a>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.querySelector('.password-strength-bar');

        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            let strength = 0;

            if (password.length >= 8) strength += 25;
            if (/[a-z]/.test(password)) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password) || /[^A-Za-z0-9]/.test(password)) strength += 25;

            strengthBar.style.width = strength + '%';
            
            if (strength <= 25) strengthBar.style.background = '#f44336';
            else if (strength <= 50) strengthBar.style.background = '#ff9800';
            else if (strength <= 75) strengthBar.style.background = '#ffc107';
            else strengthBar.style.background = '#4caf50';
        });

        // Store temporary user data
        let tempUserData = null;
        const qrSection = document.getElementById('qr-section');
        const signupForm = document.getElementById('signup-form');

        // Form submission with AJAX
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;

            if (password !== confirm) {
                alert('Passwords do not match');
                return;
            }

            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                return;
            }

            // Prepare form data
            const formData = new FormData(signupForm);
            const data = {
                action: 'register',
                username: formData.get('username'),
                email: formData.get('email'),
                password: formData.get('password'),
                confirm_password: formData.get('password_confirm'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                phone: formData.get('phone')
            };

            try {
                const response = await fetch('/api/v1/auth.php?action=register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Store user data for TOTP verification
                    tempUserData = result.data;
                    
                    // Display QR code
                    document.getElementById('qr-code').src = result.data.qr_code;
                    document.getElementById('totp-secret').textContent = result.data.secret;
                    
                    // Hide form, show QR
                    signupForm.style.display = 'none';
                    qrSection.style.display = 'block';
                } else {
                    alert('Registration failed: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Registration error: ' + error.message);
            }
        });

        // TOTP verification handler
        document.getElementById('verify-totp-btn').addEventListener('click', async () => {
            const code = document.getElementById('totp-verify').value;

            if (!code || code.length !== 6) {
                alert('Please enter a valid 6-digit code');
                return;
            }

            try {
                const response = await fetch('/api/v1/auth.php?action=verify-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: tempUserData.user_id,
                        code: code
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Success! Your account is ready and you are now logged in. Redirecting...');
                    // store token if provided
                    if (result.data && result.data.token) {
                        localStorage.setItem('token', result.data.token);
                    }
                    setTimeout(() => {
                        window.location.href = '/dashboard.php';
                    }, 1500);
                } else {
                    alert('Verification failed: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Verification error: ' + error.message);
            }
        });
    </script>
</body>
</html>
