/**
 * Authentication Manager for Varta SPA
 * Handles login, signup, and OTP verification
 */

class AuthManager {
    constructor() {
        this.isAuthenticated = !!localStorage.getItem('token');
        this.user = null;
        // no longer storing temporary OTP state
        // totpRequired and tempToken unused

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
                const totp = document.getElementById('login-totp')?.value || '';

            if (!username || !password) {
                this.showError('Username and password are required');
                return;
            }

            if (!totp) {
                this.showError('TOTP code is required');
                return;
            }

            try {
                this.showLoading('login-form');

                const response = await window.api.login(username, password, totp);
                if (!response.success) {
                    this.showError(response.message || 'Login failed');
                    return;
                }

                // Login successful
                this.handleLoginSuccess(response.data);
            } catch (error) {
                console.error('Login error:', error);
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

        const qrSection = document.getElementById('signup-qr-section');
        const qrImage = document.getElementById('signup-qr-image');
        const qrSecret = document.getElementById('signup-qr-secret');
        let tempUserData = null;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('signup-username')?.value;
            const email = document.getElementById('signup-email')?.value;
            const password = document.getElementById('signup-password')?.value;
            const confirmPassword = document.getElementById('signup-confirm')?.value;
            const firstName = document.getElementById('signup-firstname')?.value || '';
            const lastName = document.getElementById('signup-lastname')?.value || '';
            const phone = document.getElementById('signup-phone')?.value || '';

            // Validation
            if (!username || !email || !password || !confirmPassword || !firstName) {
                this.showError('Please fill in all required fields');
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

                const response = await window.api.signup(username, email, password, firstName, lastName, phone);

                if (!response.success) {
                    this.showError(response.message || 'Signup failed');
                    return;
                }

                // store user info for otp step
                tempUserData = response.data;

                // show QR and secret
                if (qrImage && response.data.qr_code) {
                    qrImage.src = response.data.qr_code;
                }
                if (qrSecret && response.data.secret) {
                    qrSecret.textContent = response.data.secret;
                }

                // hide original form and display QR section
                form.style.display = 'none';
                if (qrSection) qrSection.style.display = 'block';
            } catch (error) {
                console.error('Signup error:', error);
                this.showError('Signup failed: ' + error.message);
            } finally {
                this.hideLoading('signup-form');
            }
        });

        // verify code after scanning QR
        document.getElementById('signup-verify-btn')?.addEventListener('click', async () => {
            const code = document.getElementById('signup-verify-code')?.value;
            if (!code || code.length !== 6) {
                this.showError('Please enter a valid 6-digit code');
                return;
            }
            try {
                this.showLoading('signup-verify-btn');
                const response = await window.api.verifyOTP(code, tempUserData?.user_id);
                if (!response.success) {
                    this.showError(response.message || 'Verification failed');
                    return;
                }
                this.showSuccess('Account created and verified! You may now log in.');
                // optionally auto-login if token present
                if (response.data && response.data.token) {
                    this.handleLoginSuccess(response.data);
                }
            } catch (error) {
                this.showError('Verification error: ' + error.message);
            } finally {
                this.hideLoading('signup-verify-btn');
            }
        });
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
