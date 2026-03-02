/**
 * SPA Router for Varta
 * Handles client-side routing without page reloads
 * Always shows URL as root domain, but manages different views internally
 */

class SpaRouter {
    constructor() {
        this.currentRoute = null;
        this.currentView = 'messages'; // Default view
        this.routes = new Map();
        this.middlewares = [];
    }

    /**
     * Register a route handler
     */
    register(path, handler) {
        this.routes.set(path, handler);
    }

    /**
     * Navigate to a route (without changing URL in address bar)
     */
    async navigate(path, state = {}) {
        // Check middlewares
        for (const middleware of this.middlewares) {
            const result = await middleware(path, state);
            if (!result) return; // Middleware rejected the navigation
        }

        // Parse the path
        const [view, id] = path.split('/').filter(Boolean);

        // Update current route
        this.currentRoute = path;
        this.currentView = view || 'messages';

        // Call route handler if exists
        const handler = this.routes.get(path) || this.routes.get(view);
        if (handler) {
            await handler(id, state);
        }

        // Keep URL at root
        if (history.replaceState) {
            history.replaceState(
                { view: this.currentView, id, state },
                '',
                '/'
            );
        }

        // Emit navigation event
        this.emit('navigate', { view: this.currentView, id, state });
    }

    /**
     * Add middleware
     */
    use(middleware) {
        this.middlewares.push(middleware);
    }

    /**
     * Event emitter
     */
    on(event, callback) {
        if (!this.listeners) this.listeners = {};
        if (!this.listeners[event]) this.listeners[event] = [];
        this.listeners[event].push(callback);
    }

    emit(event, data) {
        if (!this.listeners || !this.listeners[event]) return;
        this.listeners[event].forEach(cb => cb(data));
    }

    /**
     * Get current view
     */
    getCurrentView() {
        return this.currentView;
    }

    /**
     * Activate a tab
     */
    activateTab(tabName) {
        // Update tab button active state
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');

        // Update pane visibility
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.style.display = 'none';
        });

        document.getElementById(`${tabName}-pane`)?.style.display = 'block';
    }
}

/**
 * Create global router instance
 */
window.router = new SpaRouter();

/**
 * Setup router event listeners
 */
function setupRouter() {
    // Register view handlers
    router.register('messages', async (id) => {
        router.activateTab('messages');
        if (id) {
            // Load specific conversation
            await chat.loadConversation(id);
        } else {
            // Load conversations list
            await chat.loadConversations();
        }
    });

    router.register('contacts', async () => {
        router.activateTab('contacts');
        await contacts.loadContacts();
    });

    router.register('groups', async (id) => {
        router.activateTab('groups');
        if (id) {
            await groups.loadGroupMessages(id);
        } else {
            await groups.loadGroups();
        }
    });

    router.register('settings', async () => {
        router.activateTab('settings');
    });

    // Tab button click handlers
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const tabName = e.currentTarget.getAttribute('data-tab');
            router.navigate(`/${tabName}`);
        });
    });

    // Conversation click handlers (dynamic)
    document.addEventListener('click', (e) => {
        const conversationEl = e.target.closest('.conversation-item');
        if (conversationEl) {
            const conversationId = conversationEl.getAttribute('data-id');
            router.navigate(`/messages/${conversationId}`);
        }

        const contactEl = e.target.closest('.contact-item');
        if (contactEl) {
            const userId = contactEl.getAttribute('data-id');
            router.navigate(`/messages/${userId}`);
        }

        const groupEl = e.target.closest('.group-item');
        if (groupEl) {
            const groupId = groupEl.getAttribute('data-id');
            router.navigate(`/groups/${groupId}`);
        }
    });

    // Popstate event (browser back/forward)
    window.addEventListener('popstate', (e) => {
        const state = e.state || { view: 'messages' };
        router.navigate(`/${state.view}${state.id ? '/' + state.id : ''}`);
    });

    // Set initial route
    router.navigate('/messages');
}

/**
 * Initialize router when DOM is ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupRouter);
} else {
    setupRouter();
}
