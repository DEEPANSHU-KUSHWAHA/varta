/**
 * Contacts Manager for Varta SPA
 * Handles contact list, search, and management
 */

class ContactsManager {
    constructor() {
        this.contacts = [];
        this.searchResults = [];
        this.isSearching = false;
    }

    /**
     * Initialize contacts manager
     */
    init() {
        this.setupSearchListener();
        this.setupAddContactButton();
    }

    /**
     * Load contacts list
     */
    async loadContacts() {
        try {
            const response = await window.api.getContacts(1, 100);

            if (response.success) {
                this.contacts = response.data || [];
                this.renderContactsList();
            }
        } catch (error) {
            console.error('Failed to load contacts:', error);
            this.showError('Failed to load contacts');
        }
    }

    /**
     * Setup search listener
     */
    setupSearchListener() {
        // This will be dynamically added to the search input in the contacts pane
        document.addEventListener('input', async (e) => {
            if (e.target.id === 'contact-search') {
                const query = e.target.value.trim();

                if (query.length === 0) {
                    this.isSearching = false;
                    this.renderContactsList();
                } else {
                    await this.searchUsers(query);
                }
            }
        });
    }

    /**
     * Setup add contact button
     */
    setupAddContactButton() {
        document.addEventListener('click', async (e) => {
            if (e.target.id === 'add-contact-btn') {
                this.showAddContactModal();
            }

            // Add contact from search results
            if (e.target.classList.contains('add-btn')) {
                const userId = e.target.getAttribute('data-user-id');
                await this.addContact(userId, e.target);
            }

            // Remove contact
            if (e.target.classList.contains('remove-btn')) {
                const userId = e.target.getAttribute('data-user-id');
                await this.removeContact(userId, e.target);
            }

            // Block/unblock contact
            if (e.target.classList.contains('block-btn')) {
                const userId = e.target.getAttribute('data-user-id');
                const isBlocked = e.target.getAttribute('data-blocked') === 'true';
                if (isBlocked) {
                    await this.unblockUser(userId, e.target);
                } else {
                    await this.blockUser(userId, e.target);
                }
            }
        });
    }

    /**
     * Search for users
     */
    async searchUsers(query) {
        try {
            const response = await window.api.searchUsers(query, 50);

            if (response.success) {
                this.searchResults = response.data || [];
                this.isSearching = true;
                this.renderSearchResults();
            }
        } catch (error) {
            console.error('Search failed:', error);
        }
    }

    /**
     * Add contact
     */
    async addContact(userId, buttonEl) {
        try {
            buttonEl.disabled = true;
            buttonEl.textContent = 'Adding...';

            const response = await window.api.addContact(userId);

            if (response.success) {
                buttonEl.textContent = 'Added';
                buttonEl.classList.add('remove-btn');
                buttonEl.classList.remove('add-btn');

                // Reload contacts
                await this.loadContacts();
                this.showSuccess('Contact added');
            } else {
                throw new Error(response.message || 'Failed to add contact');
            }
        } catch (error) {
            this.showError(error.message);
            buttonEl.disabled = false;
            buttonEl.textContent = 'Add';
        }
    }

    /**
     * Remove contact
     */
    async removeContact(userId, buttonEl) {
        if (!confirm('Remove this contact?')) return;

        try {
            buttonEl.disabled = true;
            buttonEl.textContent = 'Removing...';

            const response = await window.api.removeContact(userId);

            if (response.success) {
                await this.loadContacts();
                this.showSuccess('Contact removed');
            } else {
                throw new Error(response.message || 'Failed to remove contact');
            }
        } catch (error) {
            this.showError(error.message);
            buttonEl.disabled = false;
            buttonEl.textContent = 'Remove';
        }
    }

    /**
     * Block user
     */
    async blockUser(userId, buttonEl) {
        try {
            buttonEl.disabled = true;
            buttonEl.textContent = 'Blocking...';

            const response = await window.api.blockUser(userId);

            if (response.success) {
                buttonEl.textContent = 'Unblock';
                buttonEl.setAttribute('data-blocked', 'true');
                this.showSuccess('User blocked');
            } else {
                throw new Error(response.message || 'Failed to block user');
            }
        } catch (error) {
            this.showError(error.message);
            buttonEl.disabled = false;
            buttonEl.textContent = 'Block';
        }
    }

    /**
     * Unblock user
     */
    async unblockUser(userId, buttonEl) {
        try {
            buttonEl.disabled = true;
            buttonEl.textContent = 'Unblocking...';

            const response = await window.api.unblockUser(userId);

            if (response.success) {
                buttonEl.textContent = 'Block';
                buttonEl.setAttribute('data-blocked', 'false');
                this.showSuccess('User unblocked');
            } else {
                throw new Error(response.message || 'Failed to unblock user');
            }
        } catch (error) {
            this.showError(error.message);
            buttonEl.disabled = false;
            buttonEl.textContent = 'Unblock';
        }
    }

    /**
     * Render contacts list
     */
    renderContactsList() {
        const pane = document.getElementById('contacts-pane');
        if (!pane) return;

        pane.innerHTML = `
            <div style="padding: 12px; border-bottom: 1px solid #e5e5e5;">
                <input 
                    type="text" 
                    id="contact-search" 
                    class="search-input" 
                    placeholder="Search contacts..."
                >
                <button id="add-contact-btn" class="btn btn-primary" style="width: 100%; margin-top: 8px;">
                    + Add Contact
                </button>
            </div>
            <div id="contacts-list" class="contacts-list" style="padding: 12px;"></div>
        `;

        const list = document.getElementById('contacts-list');
        if (!list) return;

        if (this.contacts.length === 0) {
            list.innerHTML = '<div class="empty-state"><p>No contacts yet</p></div>';
            return;
        }

        this.contacts.forEach(contact => {
            const element = document.createElement('div');
            element.className = 'contact-item';
            element.setAttribute('data-id', contact.id);

            element.innerHTML = `
                <div class="contact-avatar"></div>
                <div style="flex: 1;">
                    <div class="contact-name">${this.escapeHtml(contact.username || contact.name)}</div>
                    <div class="contact-status">${contact.status || 'Offline'}</div>
                </div>
                <button 
                    class="btn-icon remove-btn" 
                    data-user-id="${contact.id}"
                    title="Remove contact"
                >
                    ✕
                </button>
            `;

            list.appendChild(element);
        });
    }

    /**
     * Render search results
     */
    renderSearchResults() {
        const pane = document.getElementById('contacts-pane');
        if (!pane) return;

        pane.innerHTML = `
            <div style="padding: 12px; border-bottom: 1px solid #e5e5e5;">
                <input 
                    type="text" 
                    id="contact-search" 
                    class="search-input" 
                    placeholder="Search contacts..."
                >
            </div>
            <div id="search-results" style="padding: 12px;"></div>
        `;

        document.getElementById('contact-search').value = '';

        const results = document.getElementById('search-results');
        if (!results) return;

        if (this.searchResults.length === 0) {
            results.innerHTML = '<div class="empty-state"><p>No users found</p></div>';
            return;
        }

        this.searchResults.forEach(user => {
            const isContact = this.contacts.some(c => c.id === user.id);
            const element = document.createElement('div');
            element.className = 'contact-item';

            element.innerHTML = `
                <div class="contact-avatar"></div>
                <div style="flex: 1;">
                    <div class="contact-name">${this.escapeHtml(user.username)}</div>
                    <div class="contact-status">${user.status || 'Offline'}</div>
                </div>
                <button 
                    class="btn-icon ${isContact ? 'remove-btn' : 'add-btn'}"
                    data-user-id="${user.id}"
                >
                    ${isContact ? '✕' : '+'}
                </button>
            `;

            results.appendChild(element);
        });
    }

    /**
     * Show add contact modal
     */
    showAddContactModal() {
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
                <h2 style="margin-bottom: 16px; color: #075e54;">Search Users</h2>
                <input 
                    type="text" 
                    id="search-modal-input" 
                    class="search-input"
                    placeholder="Enter username..."
                    style="margin-bottom: 16px;"
                >
                <div id="modal-search-results" style="max-height: 400px; overflow-y: auto; margin-bottom: 16px;"></div>
                <button 
                    onclick="this.parentElement.parentElement.remove()"
                    class="btn"
                    style="width: 100%; background: #e5e5e5; color: #111b21;"
                >
                    Cancel
                </button>
            </div>
        `;

        document.body.appendChild(modal);

        const searchInput = document.getElementById('search-modal-input');
        const resultsDiv = document.getElementById('modal-search-results');

        searchInput.addEventListener('input', async (e) => {
            const query = e.target.value.trim();
            if (query.length === 0) {
                resultsDiv.innerHTML = '';
                return;
            }

            try {
                const response = await window.api.searchUsers(query, 20);
                if (response.success) {
                    const users = response.data || [];
                    resultsDiv.innerHTML = '';

                    if (users.length === 0) {
                        resultsDiv.innerHTML = '<p style="color: #999; text-align: center;">No users found</p>';
                        return;
                    }

                    users.forEach(user => {
                        const isContact = this.contacts.some(c => c.id === user.id);
                        const el = document.createElement('div');
                        el.className = 'contact-item';

                        el.innerHTML = `
                            <div class="contact-avatar"></div>
                            <div style="flex: 1;">
                                <div class="contact-name">${this.escapeHtml(user.username)}</div>
                                <div class="contact-status">${user.status || 'Offline'}</div>
                            </div>
                            <button 
                                class="btn-icon ${isContact ? 'remove-btn' : 'add-btn'}"
                                data-user-id="${user.id}"
                                onclick="event.stopPropagation()"
                            >
                                ${isContact ? '✕' : '+'}
                            </button>
                        `;

                        resultsDiv.appendChild(el);
                    });
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        });

        // Auto-focus
        setTimeout(() => searchInput.focus(), 100);

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
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
     * Escape HTML
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Create global contacts manager instance
window.contacts = new ContactsManager();
