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

        html, body {
            width: 100%;
            height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a0e27 0%, #16213e 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            width: 100%;
            max-width: 1200px;
            align-items: center;
        }

        .login-branding {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            text-align: center;
        }

        .logo-badge-large {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .logo-image-large {
            height: 120px;
            width: 120px;
            object-fit: contain;
            filter: drop-shadow(0 0 20px rgba(0, 212, 255, 0.6));
        }

        .logo-text-large {
            font-family: 'Orbitron', monospace;
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            letter-spacing: 3px;
            line-height: 1;
        }

        .branding-tagline {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 400px;
            line-height: 1.6;
        }

        .login-form-container {
            background: rgba(26, 31, 58, 0.95);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            box-shadow: 0 0 50px rgba(0, 212, 255, 0.15);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
            font-family: 'Orbitron', monospace;
            letter-spacing: 1px;
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
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.85rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
            background: rgba(0, 212, 255, 0.05);
        }

        input::placeholder {
            color: var(--text-muted);
        }

        /* ============ TOTP SECTION ============ */
        .totp-section {
            display: none;
            padding: 1.5rem;
            background: rgba(0, 255, 136, 0.05);
            border: 2px solid var(--success);
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .totp-section.active {
            display: block;
        }

        .totp-section-title {
            color: var(--success);
            font-family: 'Orbitron', monospace;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .totp-section-desc {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .totp-input-label {
            color: var(--success);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
            font-size: 0.9rem;
        }

        .totp-code-input {
            width: 100%;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: 2px solid var(--success);
            border-radius: 8px;
            color: var(--text);
            font-size: 1.25rem;
            text-align: center;
            letter-spacing: 8px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .totp-code-input:focus {
            outline: none;
            border-color: var(--success);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
            background: rgba(0, 255, 136, 0.05);
        }

        .totp-code-input::placeholder {
            color: var(--text-muted);
            letter-spacing: normal;
        }

        .totp-helper-text {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-align: center;
        }

        /* ============ BUTTONS ============ */
        .btn {
            width: 100%;
            padding: 0.95rem;
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
            margin-top: 1rem;
        }

        .btn:hover {
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.5);
            transform: translateY(-2px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ============ ALERTS ============ */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
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

        .alert-info {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        /* ============ FOOTER ============ */
        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            color: var(--secondary);
        }

        .form-divider {
            text-align: center;
            margin: 1.5rem 0;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .login-wrapper {
                grid-template-columns: 1fr;
            }

            .login-branding {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .login-form-container {
                padding: 1.5rem;
            }

            .totp-code-input {
                letter-spacing: 6px;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Branding Section -->
        <div class="login-branding">
            <div class="logo-badge-large">
                <img src="/resources/logo/varta.png" alt="VartaSphere" class="logo-image-large">
                <div class="logo-text-large">VartaSphere</div>
            </div>
            <p class="branding-tagline">
                Secure, decentralized communication platform with advanced encryption and tactical control over your data.
            </p>
        </div>

        <!-- Form Section -->
        <div class="login-form-container">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Secure Communication Platform</p>
            </div>

            <div id="alertContainer"></div>

            <form id="loginForm">
                <!-- Credentials Section -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <!-- TOTP Section (shown only if 2FA is enabled) -->
                <div id="totpSection" class="totp-section">
                    <div class="totp-section-title">
                        <i class="fas fa-shield-alt"></i>
                        Two-Factor Authentication
                    </div>
                    <div class="totp-section-desc">
                        Your account is protected with two-factor authentication. Enter the 6-digit code from your authenticator app:
                    </div>
                    <label class="totp-input-label" for="totp_code">
                        <i class="fas fa-mobile-alt"></i> Authenticator Code
                    </label>
                    <input type="text" id="totp_code" name="totp_code" class="totp-code-input" placeholder="000000" maxlength="6" inputmode="numeric">
                    <p class="totp-helper-text">
                        <i class="fas fa-info-circle"></i> Check your authenticator app for the code
                    </p>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn">
                    <span class="spinner"></span>
                    <span id="btnText">Sign In</span>
                </button>
            </form>

            <div class="form-divider">
                ─ or ─
            </div>

            <div class="form-footer">
                Don't have an account? <a href="/public/signup.php">Create one here</a>
            </div>
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
        const spinner = document.querySelector('.spinner');

        let needs2FA = false;

        // ============ ALLOW ONLY NUMBERS IN TOTP ============
        totpInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // ============ AUTO-FOCUS TOTP WHEN SHOWN ============
        const observer = new MutationObserver(() => {
            if (totpSection.classList.contains('active')) {
                totpInput.focus();
            }
        });

        observer.observe(totpSection, { attributes: true, attributeFilter: ['class'] });

        // ============ FORM SUBMIT ============
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = emailInput.value.trim();
            const password = passwordInput.value;
            const totp_code = totpInput.value.trim();

            // ============ VALIDATION ============
            if (!email || !password) {
                showAlert('❌ Please fill in all fields', 'danger');
                return;
            }

            // If 2FA is required, validate TOTP code
            if (totpSection.classList.contains('active') && !totp_code) {
                showAlert('❌ Please enter your 6-digit authenticator code', 'danger');
                return;
            }

            if (totpSection.classList.contains('active') && totp_code.length !== 6) {
                showAlert('❌ Code must be exactly 6 digits', 'danger');
                return;
            }

            spinner.style.display = 'inline-block';
            btnText.textContent = 'Verifying...';

            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                
                // Only send TOTP if 2FA section is visible
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
                        showAlert('✓ Credentials verified. Please enter your 2FA code.', 'success');
                        totpSection.classList.add('active');
                        needs2FA = true;
                        totpInput.focus();
                    } else {
                        showAlert(`❌ ${data.message || 'Login failed'}`, 'danger');
                        // Reset form
                        totpSection.classList.remove('active');
                        totpInput.value = '';
                    }
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('❌ Network error. Please try again.', 'danger');
            } finally {
                spinner.style.display = 'none';
                btnText.textContent = 'Sign In';
            }
        });

        // ============ SHOW ALERT ============
        function showAlert(message, type) {
            const iconClass = type === 'danger' ? 'exclamation-circle' : (type === 'success' ? 'check-circle' : 'info-circle');
            alertContainer.innerHTML = `<div class="alert alert-${type}"><i class="fas fa-${iconClass}"></i> ${message}</div>`;
            alertContainer.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
