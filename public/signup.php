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
        <h2>Create Account</h2>

        <!-- Flash message display -->
        <?php show_flash(); ?>

        <!-- Step 1: Basic Account Information -->
        <form id="signup-form" method="POST">
            <div class="form-section">
                <h3>Step 1: Account Details</h3>
                
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required placeholder="First name">
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required placeholder="Email address">
                </div>

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required autocomplete="username" placeholder="Username">
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" placeholder="Confirm password">
                </div>

                <div class="optional-fields">
                    <details>
                        <summary>Additional Information (Optional)</summary>
                        
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
                            <label for="avatar">Avatar</label>
                            <input type="file" id="avatar" name="avatar" accept="image/*">
                        </div>
                    </details>
                </div>

                <button type="button" id="next-btn" class="btn btn-primary">Next: Setup 2FA</button>
            </div>
        </form>

        <p style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            Already have an account? 
            <a href="#" class="nav-option" data-page="login" style="color: var(--primary); text-decoration: none; font-weight: 600;">Login here</a>
        </p>
    </div>
</div>

<!-- TOTP Setup Modal -->
<div id="totp-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>🔐 Enable Two-Factor Authentication</h2>
            <button type="button" class="modal-close" id="close-totp-modal">&times;</button>
        </div>

        <div class="modal-body" id="totp-step">
            <h3>Step 1: Scan QR Code</h3>
            <p>Open Google Authenticator or Authy on your phone and scan this QR code:</p>
            
            <div id="qr-code-container">
                <img id="qr-code-img" alt="QR Code">
            </div>

            <h3 style="margin-top: 20px;">Step 2: Backup Secret Key</h3>
            <p style="color: #e74c3c; font-weight: bold;">⚠️ Save this key somewhere safe in case your authenticator app is lost:</p>
            <div id="secret-key-container">
                <span id="secret-key"></span>
            </div>
            <button type="button" id="copy-secret" class="btn" style="margin-top: 10px; width: 100%;">📋 Copy Secret Key</button>

            <h3 style="margin-top: 20px;">Step 3: Verify Code</h3>
            <p>Enter the 6-digit code from your authenticator app:</p>
            <input type="text" id="totp-code" maxlength="6" placeholder="000000" autocomplete="one-time-code" required>
            <small style="display: block; margin-top: 8px; color: #666;">Make sure the code hasn't expired (changes every 30 seconds)</small>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" id="back-btn" class="btn">Back</button>
                <button type="button" id="verify-totp-btn" class="btn btn-primary">✅ Verify & Create Account</button>
            </div>
        </div>

        <div id="totp-loading" style="text-align: center; padding: 40px; display: none;">
            <div class="spinner"></div>
            <p>Verifying your account...</p>
        </div>

        <div id="totp-success" style="text-align: center; padding: 40px; display: none;">
            <div class="checkmark">✅</div>
            <h3>Account Created Successfully!</h3>
            <p>Your two-factor authentication is now enabled.</p>
            <button type="button" class="btn btn-primary" style="width: 100%;">Return to Login</button>
        </div>
    </div>
</div>

<style>
#totp-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid var(--light-gray);
}

#qr-code-container {
    text-align: center;
    padding: 20px;
    background: #f5f5f5;
    border-radius: 8px;
}

#qr-code-img {
    max-width: 300px;
    height: auto;
}

#secret-key-container {
    background: #f0f0f0;
    padding: 12px;
    border-radius: 6px;
    font-family: monospace;
    border-left: 4px solid var(--primary);
    word-break: break-all;
}

#totp-code {
    text-align: center;
    letter-spacing: 0.5em;
    font-size: 24px;
    padding: 12px;
    border: 2px solid var(--light-gray);
    border-radius: 8px;
    width: 100%;
}

.checkmark {
    font-size: 48px;
    margin-bottom: 15px;
}

.spinner {
    margin: 0 auto;
}
</style>

.modal-header h2 {
    margin: 0;
    color: var(--primary);
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
    transition: color 0.3s ease;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    padding: 20px;
    max-height: 80vh;
    overflow-y: auto;
}

.optional-fields {
    margin-top: 15px;
}

.optional-fields summary {
    cursor: pointer;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 6px;
    font-weight: 600;
    color: var(--primary);
    transition: all 0.3s ease;
}

.optional-fields summary:hover {
    background: #efefef;
}

.optional-fields[open] summary {
    background: #e8f4f1;
    margin-bottom: 15px;
}

.optional-fields details > * {
    animation: slideInUp 0.3s ease-out;
}

.form-section {
    animation: fadeInScale 0.5s ease-out;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--light-gray);
    border-top: 4px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signup-form');
    const nextBtn = document.getElementById('next-btn');
    const totpModal = document.getElementById('totp-modal');
    const closeTotpModal = document.getElementById('close-totp-modal');
    const backBtn = document.getElementById('back-btn');
    const verifyTotpBtn = document.getElementById('verify-totp-btn');
    const totpCodeInput = document.getElementById('totp-code');
    const copySecretBtn = document.getElementById('copy-secret');
    const secretKeySpan = document.getElementById('secret-key');
    const qrCodeImg = document.getElementById('qr-code-img');

    // Validate form and show TOTP modal
    nextBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Basic validation
        const firstName = document.getElementById('first_name').value;
        const email = document.getElementById('email').value;
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const confirmPass = document.getElementById('confirm_password').value;

        if (!firstName || !email || !username || !password || !confirmPass) {
            alert('Please fill in all required fields');
            return;
        }

        if (password !== confirmPass) {
            alert('Passwords do not match');
            return;
        }

        // Show TOTP modal
        totpModal.style.display = 'flex';

        // Generate QR code
        try {
            const response = await fetch('/api/signup.php?action=generate-qr', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            });

            const data = await response.json();
            if (data.success) {
                qrCodeImg.src = data.qr_code_url;
                secretKeySpan.textContent = data.secret_key;
            } else {
                alert('Error generating QR code: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error generating QR code');
        }
    });

    // Copy secret key
    copySecretBtn.addEventListener('click', function() {
        const secretKey = secretKeySpan.textContent;
        navigator.clipboard.writeText(secretKey).then(() => {
            copySecretBtn.textContent = '✅ Copied!';
            setTimeout(() => {
                copySecretBtn.textContent = '📋 Copy Secret Key';
            }, 2000);
        });
    });

    // Verify TOTP
    verifyTotpBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const totpCode = totpCodeInput.value;
        if (!totpCode || totpCode.length !== 6) {
            alert('Please enter a valid 6-digit code');
            return;
        }

        // Show loading state
        document.getElementById('totp-step').style.display = 'none';
        document.getElementById('totp-loading').style.display = 'block';

        try {
            // Prepare form data
            const formData = new FormData(signupForm);
            formData.append('totp', totpCode);
            formData.append('action', 'verify-signup');

            const response = await fetch('/api/signup.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                // Show success
                document.getElementById('totp-loading').style.display = 'none';
                document.getElementById('totp-success').style.display = 'block';

                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '/public/auth.php?tab=login';
                }, 2000);
            } else {
                alert('Error: ' + data.message);
                document.getElementById('totp-loading').style.display = 'none';
                document.getElementById('totp-step').style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error creating account');
            document.getElementById('totp-loading').style.display = 'none';
            document.getElementById('totp-step').style.display = 'block';
        }
    });

    // Back button
    backBtn.addEventListener('click', function() {
        totpModal.style.display = 'none';
        document.getElementById('totp-step').style.display = 'block';
        document.getElementById('totp-loading').style.display = 'none';
        document.getElementById('totp-success').style.display = 'none';
        totpCodeInput.value = '';
    });

    // Close modal
    closeTotpModal.addEventListener('click', function() {
        totpModal.style.display = 'none';
        document.getElementById('totp-step').style.display = 'block';
        document.getElementById('totp-loading').style.display = 'none';
        document.getElementById('totp-success').style.display = 'none';
        totpCodeInput.value = '';
    });

    // Allow Enter key to submit TOTP
    totpCodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && totpCodeInput.value.length === 6) {
            verifyTotpBtn.click();
        }
    });

    // Auto-focus on TOTP code input
    totpCodeInput.addEventListener('focus', function() {
        this.select();
    });
});
</script>
