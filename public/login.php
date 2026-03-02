<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /public/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VartaSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00d4ff;
            --secondary: #ff00ff;
            --success: #00ff88;
            --danger: #ff3333;
            --dark: #0a0e27;
            --dark-light: #1a1f3a;
            --text: #e0e0e0;
            --text-muted: #888888;
            --border: #2a3050;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #16213e 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            background: rgba(26, 31, 58, 0.9);
            border: 1px solid var(--border);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            box-shadow: 0 0 50px rgba(0, 212, 255, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .logo-image {
            height: 50px;
            width: 50px;
            object-fit: contain;
            filter: drop-shadow(0 0 8px rgba(0, 212, 255, 0.4));
        }

        .logo-text {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .login-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
            background: rgba(0, 212, 255, 0.05);
        }

        input::placeholder {
            color: var(--text-muted);
        }

        .totp-section {
            display: none;
            padding: 1rem;
            background: rgba(0, 212, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .totp-section.active {
            display: block;
        }

        .totp-section h3 {
            color: var(--primary);
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }

        .totp-section p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }

        .totp-code-input {
            text-align: center;
            letter-spacing: 4px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .btn {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--dark);
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn:hover {
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: rgba(0, 212, 255, 0.1);
            color: var(--primary);
            border: 1px solid var(--primary);
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: rgba(0, 212, 255, 0.2);
        }

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .form-footer a:hover {
            color: var(--secondary);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .alert-danger {
            background: rgba(255, 51, 51, 0.1);
            border: 1px solid var(--danger);
            color: var(--danger);
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .loading {
            display: none;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-badge">
                <img src="/resources/logo/varta.png" alt="VartaSphere" class="logo-image">
                <span class="logo-text">VartaSphere</span>
            </div>
            <h1>Welcome Back</h1>
            <p>Secure Communication Platform</p>
        </div>

        <div id="alertContainer"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <!-- TOTP Section (shown only if 2FA is enabled) -->
            <div id="totpSection" class="totp-section">
                <h3><i class="fas fa-shield-alt"></i> Two-Factor Authentication</h3>
                <p>Enter the 6-digit code from your authenticator app:</p>
                <input type="text" id="totp_code" name="totp_code" class="totp-code-input" placeholder="000000" maxlength="6" inputmode="numeric">
            </div>

            <button type="submit" class="btn">
                <span class="loading"><span class="spinner"></span></span>
                <span id="btnText">Sign In</span>
            </button>
        </form>

        <div class="form-footer">
            Don't have an account? <a href="/public/signup.php">Create one</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const alertContainer = document.getElementById('alertContainer');
        const totpSection = document.getElementById('totpSection');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const totpInput = document.getElementById('totp_code');
        const btnText = document.getElementById('btnText');
        const loadingSpinner = document.querySelector('.loading');

        // Allow only numbers in TOTP field
        totpInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const totp_code = totpInput.value.trim();

            if (!email || !password) {
                showAlert('❌ Please fill in all fields', 'danger');
                return;
            }

            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = 'Verifying...';

            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                
                // Only add TOTP if 2FA section is visible
                if (totpSection.classList.contains('active') && totp_code) {
                    formData.append('totp_code', totp_code);
                }

                const response = await fetch('/api/login.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('✓ Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/public/dashboard.php';
                    }, 1000);
                } else {
                    // Check if 2FA is required
                    if (data.totp_required) {
                        showAlert('✓ Credentials verified. Enter your 2FA code.', 'success');
                        totpSection.classList.add('active');
                        totpInput.focus();
                    } else {
                        showAlert(`❌ ${data.message || 'Login failed'}`, 'danger');
                    }
                }
            } catch (error) {
                showAlert('❌ Network error. Please try again.', 'danger');
                console.error('Login error:', error);
            } finally {
                loadingSpinner.style.display = 'none';
                btnText.textContent = 'Sign In';
            }
        });

        function showAlert(message, type) {
            alertContainer.innerHTML = `
                <div class="alert alert-${type}">
                    ${message}
                </div>
            `;
            alertContainer.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
