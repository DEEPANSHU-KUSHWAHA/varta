/**
 * Groups Manager for Varta SPA
 * Handles group conversations, member management
 */

class GroupsManager {
    constructor() {
        this.groups = [];
        this.currentGroup = null;
        this.messages = [];
    }

    /**
     * Initialize groups manager
     */
    init() {
        this.setupCreateGroupButton();
    }

    /**
     * Load groups list
     */
    async loadGroups() {
        try {
            const response = await window.api.listGroups(1, 50);

            if (response.success) {
                this.groups = response.data || [];
                this.renderGroupsList();

                // Load first group by default
                if (this.groups.length > 0) {
                    await this.loadGroupMessages(this.groups[0].id);
                } else {
                    this.showEmptyState();
                }
            }
        } catch (error) {
            console.error('Failed to load groups:', error);
            this.showError('Failed to load groups');
        }
    }

    /**
     * Load group messages
     */
    async loadGroupMessages(groupId) {
        try {
            const response = await window.api.getGroup(groupId);

            if (response.success) {
                this.currentGroup = response.data;
                
                // Load messages for group
                const messagesResponse = await window.api.fetchMessages(groupId, 1, 30);
                if (messagesResponse.success) {
                    this.messages = messagesResponse.data || [];
                }

                this.renderGroupMessages();
                this.updateGroupHeader();
            }
        } catch (error) {
            console.error('Failed to load group messages:', error);
            this.showError('Failed to load group messages');
        }
    }

    /**
     * Setup create group button
     */
    setupCreateGroupButton() {
        document.addEventListener('click', async (e) => {
            if (e.target.id === 'create-group-btn') {
                this.showCreateGroupModal();
            }
        });
    }

    /**
     * Show create group modal
     */
    showCreateGroupModal() {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay show';
        modal.innerHTML = `
            <div style="
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border-radius: 8px;
                padding: 24px;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            ">
                <h2 style="margin-bottom: 16px; color: #075e54;">Create Group</h2>
                <form id="create-group-form" style="margin-bottom: 16px;">
                    <div class="form-group">
                        <label for="group-name">Group Name</label>
                        <input 
                            type="text" 
                            id="group-name" 
                            required
                            placeholder="Enter group name"
                        />
                    </div>
                    <div class="form-group">
                        <label for="group-description">Description (Optional)</label>
                        <textarea 
                            id="group-description"
                            placeholder="Group description"
                            rows="3"
                        ></textarea>
                    </div>
                    <div class="form-group">
                        <label>Select Members</label>
                        <div id="members-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #e5e5e5; border-radius: 6px; padding: 8px;"></div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Create</button>
                        <button type="button" class="btn" style="flex: 1; background: #e5e5e5; color: #111b21;" onclick="this.closest('.modal-overlay').remove();">Cancel</button>
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modal);

        // Load contacts for group members
        this.loadGroupMembersSelect();

        // Setup form submission
        document.getElementById('create-group-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.createGroup(modal);
        });

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    /**
     * Load contacts for group members selection
     */
    async loadGroupMembersSelect() {
        try {
            const response = await window.api.getContacts(1, 100);
            const membersList = document.getElementById('members-list');

            if (!membersList) return;

            if (response.success && response.data.length > 0) {
                membersList.innerHTML = '';

                response.data.forEach(contact => {
                    const label = document.createElement('label');
                    label.style.display = 'flex';
                    label.style.alignItems = 'center';
                    label.style.padding = '8px';
                    label.style.cursor = 'pointer';
                    label.style.borderRadius = '4px';
                    label.style.transition = 'background 0.2s';

                    label.innerHTML = `
                        <input 
                            type="checkbox" 
                            value="${contact.id}"
                            class="member-checkbox"
                            style="margin-right: 8px; cursor: pointer;"
                        />
                        <span>${this.escapeHtml(contact.username)}</span>
                    `;

                    label.addEventListener('mouseover', () => label.style.background = '#f0f0f0');
                    label.addEventListener('mouseout', () => label.style.background = 'transparent');

                    membersList.appendChild(label);
                });
            } else {
                membersList.innerHTML = '<p style="color: #999; text-align: center;">No contacts available</p>';
            }
        } catch (error) {
            console.error('Failed to load contacts:', error);
            document.getElementById('members-list').innerHTML = '<p style="color: #f44336;">Failed to load contacts</p>';
        }
    }

    /**
     * Create a group
     */
    async createGroup(modalEl) {
        const name = document.getElementById('group-name')?.value.trim();
        const description = document.getElementById('group-description')?.value.trim();
        const memberCheckboxes = document.querySelectorAll('.member-checkbox:checked');
        const memberIds = Array.from(memberCheckboxes).map(cb => cb.value);

        if (!name) {
            alert('Please enter a group name');
            return;
        }

        try {
            const response = await window.api.createGroup(name, description, memberIds);

            if (response.success) {
                this.showSuccess('Group created');
                await this.loadGroups();
                modalEl.remove();
            } else {
                this.showError(response.message || 'Failed to create group');
            }
        } catch (error) {
            this.showError('Failed to create group: ' + error.message);
        }
    }

    /**
     * Render groups list
     */
    renderGroupsList() {
        const pane = document.getElementById('groups-pane');
        if (!pane) return;

        pane.innerHTML = `
            <div style="padding: 12px; border-bottom: 1px solid #e5e5e5;">
                <button id="create-group-btn" class="btn btn-primary" style="width: 100%;">
                    + Create Group
                </button>
            </div>
            <div class="groups-list" id="groups-list" style="padding: 12px;"></div>
        `;

        const list = document.getElementById('groups-list');
        if (!list) return;

        if (this.groups.length === 0) {
            list.innerHTML = '<div class="empty-state"><p>No groups yet</p></div>';
            return;
        }

        this.groups.forEach(group => {
            const isActive = this.currentGroup?.id === group.id;
            const element = document.createElement('div');
            element.className = `group-item ${isActive ? 'active' : ''}`;
            element.setAttribute('data-id', group.id);

            element.innerHTML = `
                <div class="conversation-avatar" style="background: #075e54; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                    ${group.name.charAt(0).toUpperCase()}
                </div>
                <div style="flex: 1;">
                    <div class="contact-name">${this.escapeHtml(group.name)}</div>
                    <div class="contact-status">${group.member_count || 0} members</div>
                </div>
            `;

            list.appendChild(element);
        });
    }

    /**
     * Render group messages
     */
    renderGroupMessages() {
        const container = document.querySelector('.messages-container');
        if (!container) return;

        container.innerHTML = '';

        if (this.messages.length === 0) {
            this.showEmptyState();
            return;
        }

        this.messages.forEach(msg => {
            const isSent = msg.sender_id === window.api.userId;
            const msgEl = document.createElement('div');
            msgEl.className = `message ${isSent ? 'sent' : 'received'}`;

            msgEl.innerHTML = `
                <div>
                    ${!isSent ? `<div style="font-size: 12px; color: #075e54; margin-bottom: 4px;">${this.escapeHtml(msg.sender_name)}</div>` : ''}
                    <div class="message-bubble">
                        ${this.escapeHtml(msg.content)}
                    </div>
                </div>
                <div class="message-time">${this.formatTime(msg.created_at)}</div>
            `;

            container.appendChild(msgEl);
        });

        container.scrollTop = container.scrollHeight;
    }

    /**
     * Update group header
     */
    updateGroupHeader() {
        if (!this.currentGroup) return;

        const header = document.querySelector('.chat-info');
        if (header) {
            header.innerHTML = `
                <h2>${this.escapeHtml(this.currentGroup.name)}</h2>
                <p class="chat-status">${this.currentGroup.member_count || 0} members</p>
            `;
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
                <i style="font-size: 48px;">👥</i>
                <h3>No groups yet</h3>
                <p>Create or join a group to start group messaging</p>
            </div>
        `;
    }

    /**
     * Show error
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
     * Show success
     */
    showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert success show';
        alert.textContent = message;
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '1000';

        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 5000);
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
}

// Create global groups manager instance
window.groups = new GroupsManager();
