/**
 * Chat Manager for Varta SPA
 * Handles conversations, messages, and real-time updates
 */

class ChatManager {
    constructor() {
        this.conversations = [];
        this.currentConversation = null;
        this.messages = [];
        this.typingIndicator = null;
        this.pollInterval = null;
        this.typingPollInterval = null;
        this.pageSize = 30;
        this.currentPage = 1;
    }

    /**
     * Initialize chat manager
     */
    init() {
        this.setupMessageForm();
        this.setupScrollListener();
        this.startPolling();
    }

    /**
     * Load list of conversations
     */
    async loadConversations() {
        try {
            const response = await window.api.getConversations(1, 50);

            if (response.success) {
                this.conversations = response.data || [];
                this.renderConversationList();
                
                // Load first conversation by default
                if (this.conversations.length > 0) {
                    await this.loadConversation(this.conversations[0].id);
                } else {
                    this.showEmptyState();
                }
            }
        } catch (error) {
            console.error('Failed to load conversations:', error);
            this.showError('Failed to load conversations');
        }
    }

    /**
     * Load a specific conversation and its messages
     */
    async loadConversation(conversationId) {
        try {
            this.currentConversation = this.conversations.find(c => c.id === conversationId);

            if (!this.currentConversation) {
                // Try to fetch the conversation
                const response = await window.api.getUser(conversationId);
                if (response.success) {
                    this.currentConversation = response.data;
                }
            }

            // Load messages
            await this.loadMessages(1);

            // Update header
            this.updateChatHeader();

            // Focus message input
            document.getElementById('message-input')?.focus();
        } catch (error) {
            console.error('Failed to load conversation:', error);
            this.showError('Failed to load conversation');
        }
    }

    /**
     * Load messages for current conversation
     */
    async loadMessages(page = 1) {
        if (!this.currentConversation) return;

        try {
            const response = await window.api.fetchMessages(
                this.currentConversation.id,
                page,
                this.pageSize
            );

            if (response.success) {
                this.messages = response.data || [];
                this.currentPage = page;
                this.renderMessages();
                this.markMessagesAsRead();
            }
        } catch (error) {
            console.error('Failed to load messages:', error);
        }
    }

    /**
     * Setup message form
     */
    setupMessageForm() {
        const form = document.getElementById('message-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.sendMessage();
        });

        // Auto-expand textarea
        const input = document.getElementById('message-input');
        if (input) {
            input.addEventListener('input', (e) => {
                e.target.style.height = 'auto';
                e.target.style.height = Math.min(e.target.scrollHeight, 120) + 'px';
            });

            // Send typing indicator
            input.addEventListener('input', () => {
                if (this.currentConversation) {
                    this.sendTypingIndicator();
                }
            });
        }
    }

    /**
     * Send a message
     */
    async sendMessage() {
        const input = document.getElementById('message-input');
        const content = input?.value.trim();

        if (!content || !this.currentConversation) {
            return;
        }

        // Create optimistic message
        const optimisticMessage = {
            id: 'temp-' + Date.now(),
            sender_id: window.api.userId,
            content: content,
            type: 'text',
            created_at: new Date().toISOString(),
            status: 'sending'
        };

        // Add to messages and render
        this.messages.push(optimisticMessage);
        this.renderMessages();
        input.value = '';
        input.style.height = 'auto';

        try {
            const response = await window.api.sendMessage(
                this.currentConversation.id,
                'text',
                content
            );

            if (response.success) {
                // Update optimistic message with real data
                optimisticMessage.id = response.data.id;
                optimisticMessage.status = 'sent';
                this.renderMessages();
            } else {
                optimisticMessage.status = 'failed';
                this.renderMessages();
                this.showError('Failed to send message');
            }
        } catch (error) {
            optimisticMessage.status = 'failed';
            this.renderMessages();
            this.showError('Failed to send message');
        }
    }

    /**
     * Send typing indicator
     */
    async sendTypingIndicator() {
        if (!this.currentConversation) return;

        try {
            await window.api.setTyping(this.currentConversation.id);
        } catch (error) {
            // Silent fail for typing indicator
        }
    }

    /**
     * Mark messages as read
     */
    async markMessagesAsRead() {
        for (const message of this.messages) {
            if (message.sender_id !== window.api.userId && !message.read_at) {
                try {
                    await window.api.markMessageAsRead(message.id);
                } catch (error) {
                    // Silent fail
                }
            }
        }
    }

    /**
     * Render conversation list
     */
    renderConversationList() {
        const conversationList = document.getElementById('messages-pane')
            ?.querySelector('.conversation-list');

        if (!conversationList) {
            const pane = document.getElementById('messages-pane');
            if (pane) {
                pane.innerHTML = `
                    <div class="search-box">
                        <input type="text" class="search-input" id="conversation-search" placeholder="Search conversations...">
                    </div>
                    <div class="conversation-list" id="conversation-list"></div>
                `;
            }
        }

        const list = document.getElementById('conversation-list');
        if (!list) return;

        list.innerHTML = '';

        if (this.conversations.length === 0) {
            list.innerHTML = '<div class="empty-state"><p>No conversations yet</p></div>';
            return;
        }

        this.conversations.forEach(conv => {
            const isActive = this.currentConversation?.id === conv.id;
            const element = document.createElement('div');
            element.className = `conversation-item ${isActive ? 'active' : ''}`;
            element.setAttribute('data-id', conv.id);

            element.innerHTML = `
                <div class="conversation-avatar"></div>
                <div class="conversation-info">
                    <div class="conversation-name">${this.escapeHtml(conv.name || conv.username)}</div>
                    <div class="conversation-preview">${this.escapeHtml(conv.last_message || 'No messages yet')}</div>
                </div>
                ${conv.unread_count ? `<div class="conversation-unread">${conv.unread_count}</div>` : ''}
                <div class="conversation-time">${this.formatTime(conv.updated_at)}</div>
            `;

            list.appendChild(element);
        });
    }

    /**
     * Render messages
     */
    renderMessages() {
        const container = document.querySelector('.messages-container');
        if (!container) return;

        if (!this.currentConversation) {
            this.showEmptyState();
            return;
        }

        container.innerHTML = '';

        if (this.messages.length === 0) {
            this.showEmptyState();
            return;
        }

        this.messages.forEach(msg => {
            const isSent = msg.sender_id === window.api.userId;
            const msgEl = document.createElement('div');
            msgEl.className = `message ${isSent ? 'sent' : 'received'}`;
            msgEl.setAttribute('data-id', msg.id);

            const statusIcon = isSent ? this.getStatusIcon(msg.status) : '';

            msgEl.innerHTML = `
                <div class="message-bubble">
                    ${this.escapeHtml(msg.content)}
                </div>
                <div style="display: flex; flex-direction: column; gap: 4px; font-size: 12px;">
                    <div class="message-time">${this.formatTime(msg.created_at)}</div>
                    ${statusIcon ? `<div class="message-status">${statusIcon}</div>` : ''}
                </div>
            `;

            container.appendChild(msgEl);
        });

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    /**
     * Update chat header
     */
    updateChatHeader() {
        if (!this.currentConversation) return;

        const header = document.querySelector('.chat-info');
        if (header) {
            header.innerHTML = `
                <h2>${this.escapeHtml(this.currentConversation.name || this.currentConversation.username)}</h2>
                <p class="chat-status">${this.currentConversation.status || 'Online'}</p>
            `;
        }
    }

    /**
     * Setup scroll listener for pagination
     */
    setupScrollListener() {
        const container = document.querySelector('.messages-container');
        if (!container) return;

        container.addEventListener('scroll', async () => {
            if (container.scrollTop === 0 && this.currentPage > 1) {
                // Load previous page
                await this.loadMessages(this.currentPage - 1);
            }
        });
    }

    /**
     * Start polling for new messages
     */
    startPolling() {
        if (this.pollInterval) clearInterval(this.pollInterval);

        this.pollInterval = setInterval(async () => {
            if (this.currentConversation) {
                try {
                    const response = await window.api.getConversations(1, 50);
                    if (response.success) {
                        this.conversations = response.data || [];
                        
                        // Check for new messages
                        const currentConv = this.conversations.find(c => c.id === this.currentConversation.id);
                        if (currentConv && currentConv.last_message_id !== this.messages[this.messages.length - 1]?.id) {
                            await this.loadMessages(1);
                        }

                        // Update unread badge
                        this.updateUnreadBadge();
                    }
                } catch (error) {
                    // Silent fail for polling
                }
            }
        }, 3000); // Poll every 3 seconds
    }

    /**
     * Update unread badge
     */
    updateUnreadBadge() {
        const totalUnread = this.conversations.reduce((sum, conv) => sum + (conv.unread_count || 0), 0);
        const badge = document.querySelector('[data-tab="messages"] .badge');

        if (totalUnread > 0) {
            if (!badge) {
                const btn = document.querySelector('[data-tab="messages"]');
                if (btn) {
                    const newBadge = document.createElement('div');
                    newBadge.className = 'badge';
                    newBadge.textContent = totalUnread;
                    btn.appendChild(newBadge);
                }
            } else {
                badge.textContent = totalUnread;
            }
        } else if (badge) {
            badge.remove();
        }
    }

    /**
     * Show empty state
     */
    showEmptyState() {
        const container = document.querySelector('.messages-container');
        if (!container) return;

        container.innerHTML = `
            <div class="empty-state">
                <i style="font-size: 48px;">💬</i>
                <h3>No messages yet</h3>
                <p>Select a conversation to start messaging</p>
            </div>
        `;
    }

    /**
     * Show error message
     */
    showError(message) {
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
     * Get status icon
     */
    getStatusIcon(status) {
        switch(status) {
            case 'sending': return '↗️';
            case 'sent': return '✓';
            case 'read': return '✓✓';
            case 'failed': return '✗';
            default: return '';
        }
    }

    /**
     * Format time
     */
    formatTime(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'now';
        if (minutes < 60) return `${minutes}m`;
        if (hours < 24) return `${hours}h`;
        if (days < 7) return `${days}d`;

        return date.toLocaleDateString();
    }

    /**
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Cleanup
     */
    destroy() {
        if (this.pollInterval) clearInterval(this.pollInterval);
        if (this.typingPollInterval) clearInterval(this.typingPollInterval);
    }
}

// Create global chat manager instance
window.chat = new ChatManager();
