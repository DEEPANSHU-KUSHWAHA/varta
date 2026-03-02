<?php
/**
 * Varta - Single Page Application (SPA) Entry Point
 * All routing is handled client-side with HTML5 History API
 * This file serves as the main container for the entire app
 */

session_start();

// Check if user is authenticated
$isAuthenticated = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#075e54">
    <meta name="description" content="Varta - Modern Messaging Platform">
    <title>Varta - Messaging</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/public/css/spa.css">
    
    <style>
        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <!-- Auth Container (Hidden if authenticated) -->
    <div id="auth-container" class="auth-wrapper" style="<?php echo $isAuthenticated ? 'display: none;' : 'display: flex;'; ?>">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Varta</h1>
                <p>Modern Messaging Platform</p>
            </div>

            <ul class="tab-header">
                <li><button class="tab-link active" data-tab="login">Login</button></li>
                <li><button class="tab-link" data-tab="signup">Sign Up</button></li>
            </ul>

            <!-- Login Tab -->
            <div id="login-tab" class="tab-content active">
                <form id="login-form">
                    <div class="form-group">
                        <label for="login-username">Username or Email</label>
                        <input 
                            type="text" 
                            id="login-username" 
                            name="username"
                            required
                            placeholder="Enter your username or email"
                            autocomplete="username"
                        />
                    </div>

                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input 
                            type="password" 
                            id="login-password" 
                            name="password"
                            required
                            placeholder="Enter your password"
                            autocomplete="current-password"
                        />
                    </div>

                    <button type="submit" class="btn btn-primary w-100" style="margin-bottom: 16px;">
                        Login
                    </button>

                    <p style="text-align: center; color: #999; font-size: 12px;">
                        <a href="#" style="color: #075e54; text-decoration: none;">Forgot password?</a>
                    </p>
                </form>
            </div>

            <!-- Signup Tab -->
            <div id="signup-tab" class="tab-content">
                <form id="signup-form">
                    <div class="form-group">
                        <label for="signup-username">Username</label>
                        <input 
                            type="text" 
                            id="signup-username" 
                            name="username"
                            required
                            placeholder="Choose a username"
                            autocomplete="username"
                        />
                    </div>

                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <input 
                            type="email" 
                            id="signup-email" 
                            name="email"
                            required
                            placeholder="Enter your email"
                            autocomplete="email"
                        />
                    </div>

                    <div class="form-group">
                        <label for="signup-password">Password</label>
                        <input 
                            type="password" 
                            id="signup-password" 
                            name="password"
                            required
                            placeholder="Create a password"
                            autocomplete="new-password"
                        />
                    </div>

                    <div class="form-group">
                        <label for="signup-confirm">Confirm Password</label>
                        <input 
                            type="password" 
                            id="signup-confirm" 
                            name="confirm"
                            required
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                        />
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- App Container (Hidden if not authenticated) -->
    <div id="app-container" class="app-layout" style="<?php echo $isAuthenticated ? 'display: flex;' : 'display: none;'; ?>">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- Sidebar Header with User Info -->
            <div class="sidebar-header">
                <div class="user-profile">
                    <div class="avatar"></div>
                    <div style="flex: 1;">
                        <h3><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></h3>
                        <p class="status online">Online</p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="btn-icon" id="settings-btn" title="Settings">⚙️</button>
                </div>
            </div>

            <!-- Sidebar Tabs Navigation -->
            <div class="sidebar-tabs">
                <button class="tab-btn active" data-tab="messages" title="Messages">💬</button>
                <button class="tab-btn" data-tab="contacts" title="Contacts">👤</button>
                <button class="tab-btn" data-tab="groups" title="Groups">👥</button>
                <button class="tab-btn" data-tab="settings" title="Settings">⚙️</button>
            </div>

            <!-- Sidebar Content Panes -->
            <div class="sidebar-content">

                <!-- Messages Tab -->
                <div id="messages-pane" class="tab-pane" style="display: block;">
                    <div class="search-box">
                        <input 
                            type="text" 
                            class="search-input" 
                            id="conversation-search"
                            placeholder="Search conversations..."
                        />
                    </div>
                    <div class="conversation-list" id="conversation-list"></div>
                </div>

                <!-- Contacts Tab -->
                <div id="contacts-pane" class="tab-pane" style="display: none;">
                    <div style="padding: 12px;">
                        <input 
                            type="text" 
                            id="contact-search" 
                            class="search-input" 
                            placeholder="Search contacts..."
                        />
                        <button id="add-contact-btn" class="btn btn-primary w-100" style="margin-top: 8px;">
                            + Add Contact
                        </button>
                    </div>
                    <div id="contacts-list" class="contacts-list" style="padding: 12px;"></div>
                </div>

                <!-- Groups Tab -->
                <div id="groups-pane" class="tab-pane" style="display: none;">
                    <div style="padding: 12px;">
                        <button id="create-group-btn" class="btn btn-primary w-100">
                            + Create Group
                        </button>
                    </div>
                    <div class="groups-list" id="groups-list" style="padding: 12px;"></div>
                </div>

                <!-- Settings Tab -->
                <div id="settings-pane" class="tab-pane" style="display: none;"></div>

            </div>
        </aside>

        <!-- Chat Area -->
        <main class="chat-area">
            
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-info">
                    <h2>Select a conversation</h2>
                    <p class="chat-status">Start messaging</p>
                </div>
                <div class="chat-actions">
                    <button class="btn-icon" title="Call">☎️</button>
                    <button class="btn-icon" title="Video Call">📹</button>
                    <button class="btn-icon" title="More options">⋮</button>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="messages-container">
                <div class="empty-state">
                    <i style="font-size: 48px;">💬</i>
                    <h3>No messages yet</h3>
                    <p>Select a conversation to start messaging</p>
                </div>
            </div>

            <!-- Message Input Area -->
            <div class="message-input-area">
                <form id="message-form" class="input-group">
                    <button type="button" class="btn-icon" title="Attach file">📎</button>
                    <input 
                        type="text" 
                        id="message-input"
                        class="message-input" 
                        placeholder="Type a message..."
                        autocomplete="off"
                    />
                    <button type="button" class="btn-icon" title="Emoji">😊</button>
                    <button type="submit" class="btn-icon" title="Send">✈️</button>
                </form>
            </div>

        </main>

    </div>

    <!-- Modal Overlay (for dialogs) -->
    <div class="modal-overlay" id="modal-overlay"></div>

    <!-- JavaScript Files -->
    <script src="/public/js/api-client.js"></script>
    <script src="/public/js/router.js"></script>
    <script src="/public/js/auth.js"></script>
    <script src="/public/js/chat.js"></script>
    <script src="/public/js/contacts.js"></script>
    <script src="/public/js/groups.js"></script>
    <script src="/public/js/spa.js"></script>

</body>
</html>
