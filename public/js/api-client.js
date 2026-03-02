/**
 * API Client for Varta SPA
 * Handles all HTTP requests to the backend API
 */

class ApiClient {
    constructor(baseUrl = '/api/v1') {
        this.baseUrl = baseUrl;
        this.token = localStorage.getItem('token');
        this.userId = localStorage.getItem('userId');
    }

    /**
     * Make an API request
     * @param {string} endpoint - The API endpoint (e.g., 'auth', 'messages')
     * @param {Object} options - Fetch options
     * @returns {Promise} Response JSON
     */
    async request(endpoint, options = {}) {
        const url = new URL(`${this.baseUrl}/${endpoint}.php`, window.location.origin);

        // Add request body to URL for GET-like requests
        if (options.params) {
            Object.keys(options.params).forEach(key => {
                url.searchParams.append(key, options.params[key]);
            });
        }

        const fetchOptions = {
            method: options.method || 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        };

        // Add authentication token if available
        if (this.token) {
            fetchOptions.headers['Authorization'] = `Bearer ${this.token}`;
        }

        // Add request body
        if (options.body) {
            fetchOptions.body = JSON.stringify(options.body);
        }

        try {
            const response = await fetch(url.toString(), fetchOptions);
            const data = await response.json();

            // Handle authentication errors
            if (response.status === 401) {
                this.handleUnauthorized();
            }

            if (!response.ok && !data.success) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    /**
     * Handle unauthorized access
     */
    handleUnauthorized() {
        localStorage.removeItem('token');
        localStorage.removeItem('userId');
        localStorage.removeItem('user');
        window.location.reload();
    }

    /**
     * Set authentication token
     */
    setToken(token, userId) {
        this.token = token;
        this.userId = userId;
        localStorage.setItem('token', token);
        localStorage.setItem('userId', userId);
    }

    /**
     * Clear authentication
     */
    clearAuth() {
        this.token = null;
        this.userId = null;
        localStorage.removeItem('token');
        localStorage.removeItem('userId');
        localStorage.removeItem('user');
    }

    // ================== AUTH ENDPOINTS ==================

    async login(username, password) {
        return this.request('auth', {
            method: 'POST',
            params: { action: 'login' },
            body: { username, password }
        });
    }

    async signup(username, email, password) {
        return this.request('auth', {
            method: 'POST',
            params: { action: 'register' },
            body: { username, email, password }
        });
    }

    async verifyOTP(code) {
        return this.request('auth', {
            method: 'POST',
            params: { action: 'verify-otp' },
            body: { code }
        });
    }

    async refreshToken() {
        return this.request('auth', {
            method: 'POST',
            params: { action: 'refresh-token' }
        });
    }

    async logout() {
        return this.request('auth', {
            method: 'POST',
            params: { action: 'logout' }
        });
    }

    // ================== MESSAGES ENDPOINTS ==================

    async fetchMessages(conversationId, page = 1, limit = 30) {
        return this.request('messages', {
            method: 'GET',
            params: {
                action: 'fetch',
                conversation_id: conversationId,
                page,
                limit
            }
        });
    }

    async getConversations(page = 1, limit = 20) {
        return this.request('messages', {
            method: 'GET',
            params: {
                action: 'get-conversation',
                page,
                limit
            }
        });
    }

    async sendMessage(conversationId, type, content) {
        return this.request('messages', {
            method: 'POST',
            params: { action: 'send' },
            body: {
                conversation_id: conversationId,
                type,
                content
            }
        });
    }

    async editMessage(messageId, content) {
        return this.request('messages', {
            method: 'POST',
            params: { action: 'edit' },
            body: {
                message_id: messageId,
                content
            }
        });
    }

    async deleteMessage(messageId) {
        return this.request('messages', {
            method: 'POST',
            params: { action: 'delete' },
            body: { message_id: messageId }
        });
    }

    async markMessageAsRead(messageId) {
        return this.request('messages', {
            method: 'POST',
            params: { action: 'mark-read' },
            body: { message_id: messageId }
        });
    }

    async setTyping(conversationId) {
        return this.request('messages', {
            method: 'POST',
            params: { action: 'set-typing' },
            body: { conversation_id: conversationId }
        });
    }

    // ================== USERS ENDPOINTS ==================

    async getUserProfile() {
        return this.request('users', {
            method: 'GET',
            params: { action: 'profile' }
        });
    }

    async updateProfile(data) {
        return this.request('users', {
            method: 'POST',
            params: { action: 'update-profile' },
            body: data
        });
    }

    async getContacts(page = 1, limit = 50) {
        return this.request('users', {
            method: 'GET',
            params: {
                action: 'contacts',
                page,
                limit
            }
        });
    }

    async searchUsers(query, limit = 20) {
        return this.request('users', {
            method: 'GET',
            params: {
                action: 'search',
                query,
                limit
            }
        });
    }

    async getUser(userId) {
        return this.request('users', {
            method: 'GET',
            params: {
                action: 'get-user',
                user_id: userId
            }
        });
    }

    async addContact(userId) {
        return this.request('users', {
            method: 'POST',
            params: { action: 'add-contact' },
            body: { user_id: userId }
        });
    }

    async removeContact(userId) {
        return this.request('users', {
            method: 'POST',
            params: { action: 'remove-contact' },
            body: { user_id: userId }
        });
    }

    async blockUser(userId) {
        return this.request('users', {
            method: 'POST',
            params: { action: 'block-user' },
            body: { user_id: userId }
        });
    }

    async unblockUser(userId) {
        return this.request('users', {
            method: 'POST',
            params: { action: 'unblock-user' },
            body: { user_id: userId }
        });
    }

    async setStatus(status) {
        return this.request('users', {
            method: 'POST',
            params: { action: 'set-status' },
            body: { status }
        });
    }

    // ================== GROUPS ENDPOINTS ==================

    async listGroups(page = 1, limit = 50) {
        return this.request('groups', {
            method: 'GET',
            params: {
                action: 'list',
                page,
                limit
            }
        });
    }

    async createGroup(name, description, memberIds = []) {
        return this.request('groups', {
            method: 'POST',
            params: { action: 'create' },
            body: {
                name,
                description,
                member_ids: memberIds
            }
        });
    }

    async getGroup(groupId) {
        return this.request('groups', {
            method: 'GET',
            params: {
                action: 'get',
                group_id: groupId
            }
        });
    }

    async updateGroup(groupId, data) {
        return this.request('groups', {
            method: 'POST',
            params: { action: 'update' },
            body: {
                group_id: groupId,
                ...data
            }
        });
    }

    async deleteGroup(groupId) {
        return this.request('groups', {
            method: 'POST',
            params: { action: 'delete' },
            body: { group_id: groupId }
        });
    }

    async addGroupMember(groupId, userId, role = 'member') {
        return this.request('groups', {
            method: 'POST',
            params: { action: 'add-member' },
            body: {
                group_id: groupId,
                user_id: userId,
                role
            }
        });
    }

    async removeGroupMember(groupId, userId) {
        return this.request('groups', {
            method: 'POST',
            params: { action: 'remove-member' },
            body: {
                group_id: groupId,
                user_id: userId
            }
        });
    }

    async getGroupMembers(groupId, page = 1, limit = 50) {
        return this.request('groups', {
            method: 'GET',
            params: {
                action: 'get-members',
                group_id: groupId,
                page,
                limit
            }
        });
    }

    // ================== NOTIFICATIONS ENDPOINTS ==================

    async getNotifications(page = 1, limit = 20) {
        return this.request('notifications', {
            method: 'GET',
            params: {
                action: 'list',
                page,
                limit
            }
        });
    }

    async markNotificationAsRead(notificationId) {
        return this.request('notifications', {
            method: 'POST',
            params: { action: 'mark-read' },
            body: { notification_id: notificationId }
        });
    }

    async deleteNotification(notificationId) {
        return this.request('notifications', {
            method: 'POST',
            params: { action: 'delete' },
            body: { notification_id: notificationId }
        });
    }

    async getUnreadCount() {
        return this.request('notifications', {
            method: 'GET',
            params: { action: 'unread-count' }
        });
    }
}

// Create global API client instance
window.api = new ApiClient();
