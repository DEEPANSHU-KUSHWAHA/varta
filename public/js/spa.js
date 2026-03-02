/**
 * Main SPA Application for Varta
 * Coordinates all modules and manages application lifecycle
 */

class VartaSPA {
    constructor() {
        this.initialized = false;
        this.user = null;
    }

    /**
     * Initialize the application
     */
    async init() {
        if (this.initialized) return;

        try {
            // Check authentication
            const token = localStorage.getItem('token');
            if (!token) {
                return;
            }

            // Load user profile
            try {
                const response = await window.api.getUserProfile();
                if (response.success) {
                    this.user = response.data;
                    localStorage.setItem('user', JSON.stringify(this.user));
                }
            } catch (error) {
                console.error('Failed to load user profile:', error);
            }

            // Initialize modules
            this.setupEventListeners();
            window.router.init();
            window.chat.init();
            window.contacts.init();
            window.groups.init();

            // Load initial data
            await window.chat.loadConversations();

            // Setup settings
            this.setupSettings();

            this.initialized = true;

            console.log('Varta SPA initialized');
        } catch (error) {
            console.error('Failed to initialize app:', error);
            this.handleError('Failed to initialize application');
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Message form
        const messageForm = document.getElementById('message-form');
        if (messageForm) {
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                window.chat.sendMessage();
            });
        }

        // Tab navigation
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = btn.getAttribute('data-tab');
                window.router.navigate(`/${tabName}`);
            });
        });

        // Settings
        document.addEventListener('click', (e) => {
            if (e.target.id === 'logout-btn') {
                this.logout();
            }

            if (e.target.id === 'settings-btn' || e.target.closest('[data-tab="settings"]')) {
                this.showSettings();
            }
        });
    }

    /**
     * Setup settings
     */
    setupSettings() {
        const settingsPane = document.getElementById('settings-pane');
        if (!settingsPane) return;

        settingsPane.innerHTML = `
            <div style="padding: 20px;">
                <h3 style="color: #075e54; margin-bottom: 20px;">Settings</h3>

                <div style="margin-bottom: 20px;">
                    <h4 style="font-size: 14px; color: #111b21; margin-bottom: 12px;">Profile</h4>
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f0f0f0; border-radius: 8px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #075e54;"></div>
                        <div>
                            <div style="font-weight: 600; color: #111b21;">${this.escapeHtml(this.user?.username || 'User')}</div>
                            <div style="font-size: 12px; color: #999;">${this.escapeHtml(this.user?.email || '')}</div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <h4 style="font-size: 14px; color: #111b21; margin-bottom: 12px;">Status</h4>
                    <div style="display: flex; gap: 8px;">
                        <button id="status-online" class="btn" style="flex: 1; background: #25d366; color: white;">Online</button>
                        <button id="status-away" class="btn" style="flex: 1; background: #ff9800; color: white;">Away</button>
                        <button id="status-offline" class="btn" style="flex: 1; background: #999; color: white;">Offline</button>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <h4 style="font-size: 14px; color: #111b21; margin-bottom: 12px;">Preferences</h4>
                    <label style="display: flex; align-items: center; padding: 12px; cursor: pointer; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='transparent'">
                        <input type="checkbox" id="notifications-enabled" checked style="margin-right: 12px;" />
                        <span style="color: #111b21;">Enable Notifications</span>
                    </label>
                    <label style="display: flex; align-items: center; padding: 12px; cursor: pointer; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='transparent'">
                        <input type="checkbox" id="sound-enabled" checked style="margin-right: 12px;" />
                        <span style="color: #111b21;">Message Sounds</span>
                    </label>
                </div>

                <div style="border-top: 1px solid #e5e5e5; padding-top: 20px;">
                    <button id="logout-btn" class="btn" style="width: 100%; background: #f44336; color: white;">
                        Logout
                    </button>
                </div>

                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e5e5;">
                    <p style="font-size: 12px; color: #999; text-align: center;">
                        Varta v1.0<br />
                        © 2024 All Rights Reserved
                    </p>
                </div>
            </div>
        `;

        // Setup status buttons
        document.getElementById('status-online')?.addEventListener('click', () => this.setStatus('online'));
        document.getElementById('status-away')?.addEventListener('click', () => this.setStatus('away'));
        document.getElementById('status-offline')?.addEventListener('click', () => this.setStatus('offline'));
    }

    /**
     * Show settings
     */
    showSettings() {
        window.router.navigate('/settings');
    }

    /**
     * Set user status
     */
    async setStatus(status) {
        try {
            const response = await window.api.setStatus(status);
            if (response.success) {
                console.log('Status set to', status);
            }
        } catch (error) {
            console.error('Failed to set status:', error);
        }
    }

    /**
     * Logout
     */
    async logout() {
        if (!confirm('Are you sure you want to logout?')) return;

        try {
            await window.auth.logout();
        } catch (error) {
            console.error('Logout error:', error);
        }
    }

    /**
     * Handle errors
     */
    handleError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert error show';
        alert.textContent = message;
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '1000';

        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
    }

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Create global SPA instance
window.spa = new VartaSPA();

// Initialize when auth is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (window.auth.isAuthenticated) {
            window.spa.init();
        }
    });
} else {
    if (window.auth.isAuthenticated) {
        window.spa.init();
    }
}
