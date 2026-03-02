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
    <title>Sign Up - VartaSphere</title>
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

        .signup-container {
            width: 100%;
            max-width: 600px;
            background: rgba(26, 31, 58, 0.9);
            border: 1px solid var(--border);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            box-shadow: 0 0 50px rgba(0, 212, 255, 0.1);
        }

        .signup-header {
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

        .signup-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .signup-header p {
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
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 1rem;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: 500;
        }

        .twofa-section {
            padding: 1.5rem;
            background: rgba(0, 255, 136, 0.05);
            border: 2px solid var(--success);
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .twofa-section h3 {
            color: var(--success);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .twofa-steps {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .twofa-step {
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 6px;
            border-left: 3px solid var(--success);
        }

        .twofa-step-num {
            display: inline-block;
            background: var(--success);
            color: var(--dark);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-weight: 700;
            margin-right: 0.75rem;
        }

        .twofa-step-title {
            color: var(--success);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .twofa-step-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }

        #qr-code-container {
            text-align: center;
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
        }

        #qr-code {
            max-width: 200px;
            border: 2px solid var(--primary);
            border-radius: 8px;
            padding: 0.5rem;
            background: white;
        }

        .secret-key {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(0, 212, 255, 0.05);
            border: 1px solid var(--primary);
            border-radius: 6px;
            margin: 0.75rem 0;
        }

        .secret-key code {
            flex: 1;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            color: var(--primary);
            word-break: break-all;
        }

        .btn-copy {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            padding: 0.5rem;
            transition: transform 0.2s ease;
        }

        .btn-copy:hover {
            transform: scale(1.1);
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

        .form-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
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

        .alert-info {
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
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

        .twofa-toggle {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 28px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
            border: 1px solid var(--border);
        }

        .toggle-switch.active {
            background: var(--success);
        }

        .toggle-slider {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            transition: left 0.3s ease;
        }

        .toggle-switch.active .toggle-slider {
            left: 24px;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <div class="logo-badge">
                <img src="/resources/logo/varta.png" alt="VartaSphere" class="logo-image">
                <span class="logo-text">VartaSphere</span>
            </div>
            <h1>Create Account</h1>
            <p>Join our secure communication platform</p>
        </div>

        <div id="alertContainer"></div>

        <form id="signupForm">
            <!-- Basic Info -->
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" placeholder="Doe" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="johndoe" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone (Optional)</label>
                <input type="tel" id="phone" name="phone" placeholder="+1 (555) 000-0000">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="••••••••" required>
            </div>

            <!-- 2FA Toggle -->
            <div class="twofa-toggle">
                <label for="enable2fa">Enable Two-Factor Authentication</label>
                <div class="toggle-switch" id="toggle2fa">
                    <div class="toggle-slider"></div>
                </div>
            </div>

            <!-- 2FA Setup Section -->
            <div id="twofa-setup-section" class="twofa-section" style="display: none;">
                <h3>
                    <i class="fas fa-shield-alt"></i>
                    Two-Factor Authentication Setup
                </h3>
                <p style="color: var(--text-muted); margin-bottom: 1rem;">
                    Secure your account with two-factor authentication. You'll need an authenticator app like Google Authenticator, Microsoft Authenticator, or Authy.
                </p>

                <div class="twofa-steps">
                    <div class="twofa-step">
                        <div class="twofa-step-title">
                            <span class="twofa-step-num">1</span>
                            Download an Authenticator App
                        </div>
                        <div class="twofa-step-desc">
                            Download Google Authenticator, Microsoft Authenticator, or Authy on your phone.
                        </div>
                    </div>

                    <div class="twofa-step">
                        <div class="twofa-step-title">
                            <span class="twofa-step-num">2</span>
                            Scan the QR Code
                        </div>
                        <div class="twofa-step-desc">
                            Scan this QR code with your authenticator app:
                        </div>
                        <div id="qr-code-container">
                            <img id="qr-code" src="" alt="QR Code" style="display: none;">
                            <div id="qr-loading" style="color: var(--text-muted);">
                                <i class="fas fa-spinner fa-spin"></i> Generating QR Code...
                            </div>
                        </div>
                    </div>

                    <div class="twofa-step">
                        <div class="twofa-step-title">
                            <span class="twofa-step-num">3</span>
                            Manual Entry (if scanning fails)
                        </div>
                        <div class="twofa-step-desc">
                            Or enter this key manually in your app:
                        </div>
                        <div class="secret-key">
                            <code id="secret-key"></code>
                            <button type="button" class="btn-copy" title="Copy Secret Key">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="twofa-step">
                        <div class="twofa-step-title">
                            <span class="twofa-step-num">4</span>
                            Verify Your Code
                        </div>
                        <div class="twofa-step-desc">
                            Enter the 6-digit code from your authenticator app:
                        </div>
                        <input type="text" id="totp_verify" name="totp_verify" class="totp-code-input" placeholder="000000" maxlength="6" inputmode="numeric" style="text-align: center; letter-spacing: 4px; font-weight: 700;">
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div class="checkbox-group" style="margin: 1.5rem 0;">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the Terms and Conditions</label>
            </div>

            <button type="submit" class="btn">
                <span class="loading"><span class="spinner"></span></span>
                <span id="btnText">Create Account</span>
            </button>
        </form>

        <div class="form-footer">
            Already have an account? <a href="/public/login.php">Sign In</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('signupForm');
        const alertContainer = document.getElementById('alertContainer');
        const toggle2fa = document.getElementById('toggle2fa');
        const twofa2faSection = document.getElementById('twofa-setup-section');
        const totpVerifyInput = document.getElementById('totp_verify');
        const btnText = document.getElementById('btnText');
        const loadingSpinner = document.querySelector('.loading');

        let totpSecret = null;

        // 2FA Toggle
        toggle2fa.addEventListener('click', () => {
            toggle2fa.classList.toggle('active');
            twofa2faSection.style.display = toggle2fa.classList.contains('active') ? 'block' : 'none';
            
            if (toggle2fa.classList.contains('active')) {
                generateTOTPSecret();
            }
        });

        // Allow only numbers in TOTP field
        totpVerifyInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // Generate TOTP Secret
        async function generateTOTPSecret() {
            try {
                const response = await fetch('/api/totp_qr.php?action=generate');
                const data = await response.json();

                if (data.success) {
                    totpSecret = data.secret;
                    document.getElementById('secret-key').textContent = data.secret;
                    document.getElementById('qr-code').src = data.qr_code_url;
                    document.getElementById('qr-code').style.display = 'block';
                    document.getElementById('qr-loading').style.display = 'none';

                    // Copy button
                    document.querySelector('.btn-copy').addEventListener('click', (e) => {
                        e.preventDefault();
                        navigator.clipboard.writeText(data.secret);
                        const btn = e.target.closest('.btn-copy');
                        const originalHTML = btn.innerHTML;
                        btn.innerHTML = '<i class="fas fa-check"></i>';
                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                        }, 2000);
                    });
                } else {
                    showAlert('Error generating TOTP secret', 'danger');
                }
            } catch (error) {
                console.error('Error generating TOTP:', error);
                showAlert('Error generating TOTP secret', 'danger');
            }
        }

        // Form Submit
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate passwords match
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;

            if (password !== passwordConfirm) {
                showAlert('❌ Passwords do not match', 'danger');
                return;
            }

            // Validate 2FA if enabled
            if (toggle2fa.classList.contains('active')) {
                const totpVerify = totpVerifyInput.value.trim();
                if (!totpVerify || totpVerify.length !== 6) {
                    showAlert('❌ Please enter the 6-digit code from your authenticator app', 'danger');
                    return;
                }
            }

            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = 'Creating Account...';

            try {
                const formData = new FormData(form);
                formData.append('enable_2fa', toggle2fa.classList.contains('active') ? '1' : '0');
                
                if (toggle2fa.classList.contains('active')) {
                    formData.append('totp_secret', totpSecret);
                }

                const response = await fetch('/api/signup.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('✓ Account created successfully! Redirecting to login...', 'success');
                    setTimeout(() => {
                        window.location.href = '/public/login.php';
                    }, 2000);
                } else {
                    showAlert(`❌ ${data.message || 'Signup failed'}`, 'danger');
                }
            } catch (error) {
                showAlert('❌ Network error. Please try again.', 'danger');
                console.error('Signup error:', error);
            } finally {
                loadingSpinner.style.display = 'none';
                btnText.textContent = 'Create Account';
            }
        });

        function showAlert(message, type) {
            alertContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        }
    </script>
</body>
</html>
