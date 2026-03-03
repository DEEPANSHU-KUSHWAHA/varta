<?php
/**
 * Varta - Standalone Login Page
 * For users accessing the app without JavaScript SPA
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
    <meta name="description" content="Varta - Login to your account">
    <title>Login - Varta Messaging</title>
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
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin: 20px;
            backdrop-filter: blur(10px);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .auth-header h1 {
            font-size: 32px;
            color: var(--primary);
            font-weight: 700;
            letter-spacing: -1px;
            margin: 0;
        }

        .auth-header p {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(7, 94, 84, 0.1);
        }

        .form-group input::placeholder {
            color: #999;
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
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(7, 94, 84, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: center;
            margin-top: 16px;
        }

        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }

        .forgot-password a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .signup-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
            color: #666;
            font-size: 14px;
        }

        .signup-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .signup-link a:hover {
            color: var(--primary-dark);
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

        .alert.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .alert.info {
            background: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #2196f3;
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

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
        }

        .remember-me label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
            color: #666;
        }

        @media (max-width: 480px) {
            .auth-container {
                max-width: 100%;
                border-radius: 0;
                padding: 20px;
            }

            .auth-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>🚀 Varta</h1>
            <p>Modern Messaging Platform</p>
        </div>

        <?php show_flash(); ?>

        <form method="POST" id="login-form">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    placeholder="your@email.com or username"
                    autocomplete="email"
                />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Enter your password"
                    autocomplete="current-password"
                />
            </div>

            <div class="form-group">
                <label for="totp_code">One-Time Password (OTP)</label>
                <input 
                    type="text" 
                    id="totp_code" 
                    name="totp_code" 
                    required 
                    placeholder="6-digit code from authenticator"
                    autocomplete="one-time-code"
                    maxlength="6"
                    inputmode="numeric"
                />
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" />
                <label for="remember">Remember me for 30 days</label>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>

            <div class="forgot-password">
                <a href="/reset-password.php">Forgot your password?</a>
            </div>
        </form>

        <script>
            // AJAX login submission
            document.getElementById('login-form').addEventListener('submit', async (e) => {
                e.preventDefault();

                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const totp = document.getElementById('totp_code').value;

                if (!username || !password || !totp) {
                    alert('All fields are required');
                    return;
                }

                try {
                    const response = await fetch('/api/v1/auth.php?action=login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ username, password, totp })
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert('Login successful');
                        if (result.data && result.data.token) {
                            localStorage.setItem('token', result.data.token);
                        }
                        window.location.href = '/dashboard.php';
                    } else {
                        alert('Login failed: ' + result.message);
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error logging in.');
                }
            });
        </script>

        <div class="signup-link">
            Don't have an account? <a href="/signup-page.php">Sign up here</a>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('/api/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = '/index.php';
                } else if (data.totp_required) {
                    // Show TOTP prompt
                    alert('Please enter your 2FA code');
                } else {
                    alert(data.message || 'Login failed');
                }
            } catch (error) {
                alert('Connection error: ' + error.message);
            }
        });
    </script>
</body>
</html>
