/**
 * Authentication Manager for Varta SPA
 * Handles login, signup, and OTP verification
 */

class AuthManager {
    constructor() {
        this.isAuthenticated = !!localStorage.getItem('token');
        this.user = null;
        this.totpRequired = false;
        this.tempToken = null;
    }

    /**
     * Initialize authentication UI
     */
    init() {
        this.setupTabSwitching();
        this.setupLoginForm();
        this.setupSignupForm();
        this.checkAuthStatus();
    }

    /**
     * Setup tab switching between login and signup
     */
    setupTabSwitching() {
        const tabLinks = document.querySelectorAll('.tab-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = link.getAttribute('data-tab');

                // Remove active class from all tabs
                tabLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');

                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Show selected tab content
                document.getElementById(`${tabName}-tab`)?.classList.add('active');
            });
        });
    }

    /**
     * Setup login form
     */
    setupLoginForm() {
        const form = document.getElementById('login-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('login-username')?.value;
            const password = document.getElementById('login-password')?.value;

            if (!username || !password) {
                this.showError('Please enter username and password');
                return;
            }

            try {
                this.showLoading('login-form');

                const response = await window.api.login(username, password);

                if (!response.success) {
                    this.showError(response.message || 'Login failed');
                    return;
                }

                // Check if TOTP verification is required
                if (response.data && response.data.totp_required) {
                    this.totpRequired = true;
                    this.tempToken = response.data.temp_token;
                    this.showOTPForm();
                } else {
                    // Login successful
                    this.handleLoginSuccess(response.data);
                }
            } catch (error) {
                this.showError('Login failed: ' + error.message);
            } finally {
                this.hideLoading('login-form');
            }
        });
    }

    /**
     * Setup signup form
     */
    setupSignupForm() {
        const form = document.getElementById('signup-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('signup-username')?.value;
            const email = document.getElementById('signup-email')?.value;
            const password = document.getElementById('signup-password')?.value;
            const confirmPassword = document.getElementById('signup-confirm')?.value;

            // Validation
            if (!username || !email || !password || !confirmPassword) {
                this.showError('Please fill in all fields');
                return;
            }

            if (password !== confirmPassword) {
                this.showError('Passwords do not match');
                return;
            }

            if (password.length < 6) {
                this.showError('Password must be at least 6 characters');
                return;
            }

            try {
                this.showLoading('signup-form');

                const response = await window.api.signup(username, email, password);

                if (!response.success) {
                    this.showError(response.message || 'Signup failed');
                    return;
                }

                // Signup successful, show success message
                this.showSuccess('Account created successfully! Please log in.');

                // Reset form and switch to login
                form.reset();
                setTimeout(() => {
                    document.querySelector('[data-tab="login"]').click();
                }, 2000);
            } catch (error) {
                this.showError('Signup failed: ' + error.message);
            } finally {
                this.hideLoading('signup-form');
            }
        });
    }

    /**
     * Show OTP verification form
     */
    showOTPForm() {
        const authContainer = document.getElementById('auth-container');
        if (!authContainer) return;

        // Hide login/signup forms
        document.getElementById('auth-tabs')?.style.display = 'none';

        // Create OTP form
        const otpForm = document.createElement('div');
        otpForm.id = 'otp-form-container';
        otpForm.innerHTML = `
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color: #075e54; margin-bottom: 8px;">Two-Factor Authentication</h2>
                <p style="color: #666; font-size: 14px;">Enter the code from your authenticator app</p>
            </div>

            <form id="otp-form" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label for="otp-code">Enter Code</label>
                    <input 
                        type="text" 
                        id="otp-code" 
                        placeholder="000000" 
                        maxlength="6"
                        inputmode="numeric"
                        style="text-align: center; font-size: 20px; letter-spacing: 8px;"
                    />
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify</button>
            </form>

            <button id="back-to-login" style="width: 100%; padding: 10px; border: 1px solid #ddd; background: white; color: #075e54; border-radius: 6px; cursor: pointer; font-weight: 600;">Back to Login</button>
        `;

        authContainer.appendChild(otpForm);

        // Setup OTP form
        document.getElementById('otp-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.verifyOTP();
        });

        // Setup back button
        document.getElementById('back-to-login')?.addEventListener('click', () => {
            otpForm.remove();
            document.getElementById('auth-tabs').style.display = 'block';
            this.totpRequired = false;
            this.tempToken = null;
        });

        // Auto-focus on the code input
        setTimeout(() => document.getElementById('otp-code')?.focus(), 100);
    }

    /**
     * Verify OTP code
     */
    async verifyOTP() {
        const code = document.getElementById('otp-code')?.value;

        if (!code || code.length !== 6) {
            this.showError('Please enter a valid 6-digit code');
            return;
        }

        try {
            this.showLoading('otp-form');

            const response = await window.api.verifyOTP(code);

            if (!response.success) {
                this.showError(response.message || 'Invalid code');
                return;
            }

            // OTP verified, login successful
            this.handleLoginSuccess(response.data);
        } catch (error) {
            this.showError('Verification failed: ' + error.message);
        } finally {
            this.hideLoading('otp-form');
        }
    }

    /**
     * Handle successful login
     */
    handleLoginSuccess(data) {
        if (data && data.token) {
            window.api.setToken(data.token, data.user_id);
            this.user = data;
            this.isAuthenticated = true;

            // Save user info
            localStorage.setItem('user', JSON.stringify(data));

            // Hide auth container
            document.getElementById('auth-container')?.style.display = 'none';
            document.getElementById('app-container')?.style.display = 'flex';

            // Initialize app
            if (typeof spa !== 'undefined') {
                spa.init();
            }
        }
    }

    /**
     * Handle logout
     */
    async logout() {
        try {
            await window.api.logout();
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            window.api.clearAuth();
            this.isAuthenticated = false;
            this.user = null;

            // Show auth container
            document.getElementById('auth-container').style.display = 'flex';
            document.getElementById('app-container').style.display = 'none';

            // Reset forms
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');
            loginForm?.reset();
            signupForm?.reset();

            // Reset to login tab
            document.querySelector('[data-tab="login"]')?.click();
        }
    }

    /**
     * Check authentication status
     */
    checkAuthStatus() {
        const token = localStorage.getItem('token');
        const user = localStorage.getItem('user');

        if (token && user) {
            this.isAuthenticated = true;
            this.user = JSON.parse(user);
            window.api.token = token;

            // Show app
            document.getElementById('auth-container').style.display = 'none';
            document.getElementById('app-container').style.display = 'flex';

            // Initialize app
            if (typeof spa !== 'undefined') {
                spa.init();
            }
        } else {
            this.isAuthenticated = false;
        }
    }

    /**
     * Show loading state
     */
    showLoading(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Loading...';
            submitBtn.style.opacity = '0.7';
        }
    }

    /**
     * Hide loading state
     */
    hideLoading(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = formId === 'login-form' ? 'Login' : 'Create Account';
            submitBtn.style.opacity = '1';
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert error show';
        alert.textContent = message;
        alert.style.marginBottom = '16px';

        const authBox = document.querySelector('.auth-box');
        if (authBox) {
            authBox.insertBefore(alert, authBox.firstChild);
            setTimeout(() => alert.remove(), 5000);
        }
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert success show';
        alert.textContent = message;
        alert.style.marginBottom = '16px';

        const authBox = document.querySelector('.auth-box');
        if (authBox) {
            authBox.insertBefore(alert, authBox.firstChild);
            setTimeout(() => alert.remove(), 5000);
        }
    }
}

// Create global auth manager instance
window.auth = new AuthManager();

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.auth.init());
} else {
    window.auth.init();
}
