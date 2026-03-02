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
            overflow-x: hidden;
        }

        .signup-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            width: 100%;
            max-width: 1200px;
            align-items: center;
        }

        .signup-branding {
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

        .signup-form-container {
            background: rgba(26, 31, 58, 0.95);
            border: 1px solid var(--border);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            box-shadow: 0 0 50px rgba(0, 212, 255, 0.15);
            max-height: 90vh;
            overflow-y: auto;
        }

        .signup-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .signup-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
            font-family: 'Orbitron', monospace;
            letter-spacing: 1px;
        }

        .signup-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
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
        input[type="password"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 0.85rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
        }

        input:focus,
        select:focus {
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

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-section-title {
            font-family: 'Orbitron', monospace;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--primary);
            margin: 1.5rem 0 1rem 0;
            letter-spacing: 1px;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }

        /* ============ 2FA TOGGLE ============ */
        .twofa-toggle-section {
            padding: 1.25rem;
            background: rgba(0, 212, 255, 0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .twofa-toggle-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .twofa-toggle-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            margin: 0;
            font-weight: 600;
            color: var(--text);
        }

        .twofa-toggle-label i {
            color: var(--primary);
        }

        /* Toggle Switch */
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

        .twofa-toggle-desc {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 0.75rem;
        }

        /* ============ 2FA SETUP SECTION ============ */
        .twofa-setup-section {
            display: none;
            padding: 1.5rem;
            background: rgba(0, 255, 136, 0.05);
            border: 2px solid var(--success);
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .twofa-setup-section.active {
            display: block;
        }

        .twofa-setup-title {
            color: var(--success);
            font-family: 'Orbitron', monospace;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .twofa-steps {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .twofa-step {
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 6px;
            border-left: 3px solid var(--success);
        }

        .twofa-step-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .twofa-step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--success);
            color: var(--dark);
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .twofa-step-title {
            color: var(--success);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .twofa-step-desc {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        #qr-code-container {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            margin: 0.75rem 0;
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #qr-code {
            max-width: 200px;
            border: 3px solid var(--primary);
            border-radius: 8px;
            padding: 0.5rem;
            background: white;
        }

        #qr-loading {
            color: var(--text-muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .secret-key-box {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid var(--primary);
            border-radius: 6px;
            margin: 0.75rem 0;
        }

        .secret-key-box code {
            flex: 1;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            color: var(--primary);
            word-break: break-all;
            line-height: 1.4;
        }

        .btn-copy {
            background: none;
            border: none;
            color: var(--primary);
            cursor: pointer;
            padding: 0.5rem;
            transition: all 0.2s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-copy:hover {
            transform: scale(1.2);
            color: var(--secondary);
        }

        .totp-verify-input {
            width: 100%;
            padding: 0.85rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 1.2rem;
            margin: 0.75rem 0;
            text-align: center;
            letter-spacing: 6px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        .totp-verify-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
        }

        .totp-verify-input::placeholder {
            color: var(--text-muted);
            letter-spacing: normal;
        }

        /* ============ CHECKBOXES ============ */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.5rem 0;
        }

        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
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
            display: inline-block;
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

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .signup-wrapper {
                grid-template-columns: 1fr;
            }

            .signup-branding {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .signup-form-container {
                padding: 1.5rem;
                border-radius: 12px;
            }
        }

        /* ============ SCROLLBAR ============ */
        .signup-form-container::-webkit-scrollbar {
            width: 6px;
        }

        .signup-form-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        .signup-form-container::-webkit-scrollbar-thumb {
            background: rgba(0, 212, 255, 0.3);
            border-radius: 3px;
        }

        .signup-form-container::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 212, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="signup-wrapper">
        <!-- Branding Section -->
        <div class="signup-branding">
            <div class="logo-badge-large">
                <img src="/resources/logo/varta.png" alt="VartaSphere" class="logo-image-large">
                <div class="logo-text-large">VartaSphere</div>
            </div>
            <p class="branding-tagline">
                Secure, decentralized communication platform with advanced encryption and tactical control over your data.
            </p>
        </div>

        <!-- Form Section -->
        <div class="signup-form-container">
            <div class="signup-header">
                <h1>Create Account</h1>
                <p>Join our secure communication network</p>
            </div>

            <div id="alertContainer"></div>

            <form id="signupForm">
                <!-- Personal Info Section -->
                <div class="form-section-title">
                    <i class="fas fa-user"></i> Personal Information
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" placeholder="John" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Doe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" placeholder="johndoe" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone (Optional)</label>
                        <input type="tel" id="phone" name="phone" placeholder="+1 (555) 000-0000">
                    </div>
                </div>

                <!-- Security Section -->
                <div class="form-section-title">
                    <i class="fas fa-lock"></i> Security
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm Password *</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="••••••••" required>
                </div>

                <!-- 2FA Section -->
                <div class="form-section-title">
                    <i class="fas fa-shield-alt"></i> Two-Factor Authentication
                </div>

                <div class="twofa-toggle-section">
                    <div class="twofa-toggle-header">
                        <label class="twofa-toggle-label">
                            <i class="fas fa-mobile-alt"></i>
                            Enable 2FA (Recommended)
                        </label>
                        <div class="toggle-switch" id="toggle2fa">
                            <div class="toggle-slider"></div>
                        </div>
                    </div>
                    <p class="twofa-toggle-desc">
                        Protect your account with two-factor authentication using an authenticator app
                    </p>
                </div>

                <!-- 2FA Setup Steps -->
                <div id="twofa-setup-section" class="twofa-setup-section">
                    <div class="twofa-setup-title">
                        <i class="fas fa-cog"></i> Setup Authentication
                    </div>

                    <div class="twofa-steps">
                        <!-- Step 1 -->
                        <div class="twofa-step">
                            <div class="twofa-step-header">
                                <span class="twofa-step-num">1</span>
                                <span class="twofa-step-title">Download Authenticator App</span>
                            </div>
                            <div class="twofa-step-desc">
                                Download one of these apps: Google Authenticator, Microsoft Authenticator, or Authy
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="twofa-step">
                            <div class="twofa-step-header">
                                <span class="twofa-step-num">2</span>
                                <span class="twofa-step-title">Scan QR Code</span>
                            </div>
                            <div class="twofa-step-desc">
                                Open your authenticator app and scan this QR code:
                            </div>
                            <div id="qr-code-container">
                                <div id="qr-loading">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem;"></i>
                                    <span>Generating QR Code...</span>
                                </div>
                                <img id="qr-code" src="" alt="QR Code" style="display: none;">
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="twofa-step">
                            <div class="twofa-step-header">
                                <span class="twofa-step-num">3</span>
                                <span class="twofa-step-title">Manual Entry (If Scan Fails)</span>
                            </div>
                            <div class="twofa-step-desc">
                                Or enter this secret key manually in your authenticator app:
                            </div>
                            <div class="secret-key-box">
                                <code id="secret-key"></code>
                                <button type="button" class="btn-copy" title="Copy Secret Key">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="twofa-step">
                            <div class="twofa-step-header">
                                <span class="twofa-step-num">4</span>
                                <span class="twofa-step-title">Verify Your Code</span>
                            </div>
                            <div class="twofa-step-desc">
                                Enter the 6-digit code from your authenticator app:
                            </div>
                            <input type="text" id="totp_verify" name="totp_verify" class="totp-verify-input" placeholder="000000" maxlength="6" inputmode="numeric">
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the Terms and Conditions</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn">
                    <span class="spinner" style="display: none;"></span>
                    <span id="btnText">Create Account</span>
                </button>
            </form>

            <div class="form-footer">
                Already have an account? <a href="/public/login.php">Sign In Here</a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('signupForm');
        const alertContainer = document.getElementById('alertContainer');
        const toggle2fa = document.getElementById('toggle2fa');
        const twofa2faSection = document.getElementById('twofa-setup-section');
        const totpVerifyInput = document.getElementById('totp_verify');
        const btnText = document.getElementById('btnText');
        const spinner = document.querySelector('.spinner');

        let totpSecret = null;

        // ============ 2FA TOGGLE ============
        toggle2fa.addEventListener('click', () => {
            toggle2fa.classList.toggle('active');
            twofa2faSection.classList.toggle('active');
            
            if (toggle2fa.classList.contains('active')) {
                generateTOTPSecret();
            } else {
                totpSecret = null;
                totpVerifyInput.value = '';
            }
        });

        // ============ ALLOW ONLY NUMBERS IN TOTP ============
        totpVerifyInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        // ============ GENERATE TOTP SECRET ============
        async function generateTOTPSecret() {
            try {
                const response = await fetch('/api/totp_qr.php?action=generate');
                const data = await response.json();

                if (data.success) {
                    totpSecret = data.secret;
                    
                    // Show QR Code
                    document.getElementById('qr-code').src = data.qr_code_url;
                    document.getElementById('qr-code').style.display = 'block';
                    document.getElementById('qr-loading').style.display = 'none';
                    
                    // Show Secret Key
                    document.getElementById('secret-key').textContent = data.secret;

                    // Setup Copy Button
                    const copyBtn = document.querySelector('.btn-copy');
                    copyBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        navigator.clipboard.writeText(data.secret);
                        const originalHTML = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<i class="fas fa-check" style="color: var(--success);"></i>';
                        setTimeout(() => {
                            copyBtn.innerHTML = originalHTML;
                        }, 2000);
                    });
                } else {
                    showAlert('❌ Error generating TOTP secret: ' + data.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('❌ Error generating QR code', 'danger');
            }
        }

        // ============ FORM SUBMIT ============
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;

            // Validate passwords match
            if (password !== passwordConfirm) {
                showAlert('❌ Passwords do not match', 'danger');
                return;
            }

            // Validate password length
            if (password.length < 8) {
                showAlert('❌ Password must be at least 8 characters long', 'danger');
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

            spinner.style.display = 'inline-block';
            btnText.textContent = 'Creating Account...';

            try {
                const formData = new FormData(form);
                formData.append('enable_2fa', toggle2fa.classList.contains('active') ? '1' : '0');
                
                if (toggle2fa.classList.contains('active') && totpSecret) {
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
                    showAlert('❌ ' + (data.message || 'Signup failed'), 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('❌ Network error. Please try again.', 'danger');
            } finally {
                spinner.style.display = 'none';
                btnText.textContent = 'Create Account';
            }
        });

        // ============ SHOW ALERT ============
        function showAlert(message, type) {
            alertContainer.innerHTML = `<div class="alert alert-${type}"><i class="fas fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'}"></i> ${message}</div>`;
            alertContainer.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
