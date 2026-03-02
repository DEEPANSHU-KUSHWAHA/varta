<?php
/**
 * Varta - Modern SPA Chat Application
 * Single Page Application Entry Point
 * All content served from this file, URL always stays at root
 */
session_start();

// Check if user is authenticated
$isAuthenticated = isset($_SESSION['user_id']);

?> <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Varta - Chat Application</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/spa.css">
    <link rel="stylesheet" href="/public/css/chat.css">
    <style>
        :root {
            --primary: #075e54;
            --primary-dark: #054a3f;
            --secondary: #25d366;
            --danger: #e33371;
            --warning: #ff9500;
            --dark: #111b21;
            --light: #f0f0f0;
            --border: #e5e5e5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background: var(--dark);
            color: #333;
        }

        /* Loading spinner */
        .spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
        }

        .spinner.active {
            display: block;
        }

        .spinner-circle {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Error display */
        .alert {
            padding: 12px 16px;
            margin: 10px 0;
            border-radius: 4px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert.error {
            background: #ffebee;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .alert.success {
            background: #e8f5e9;
            color: var(--secondary);
            border-left: 4px solid var(--secondary);
        }
    </style>
</head>
<body>
    <!-- Authentication pages container -->
    <div id="auth-container" <?php echo !$isAuthenticated ? '' : 'style="display: none;"'; ?>>
        <?php if (!$isAuthenticated): ?>
            <div class="auth-wrapper">
                <div class="auth-box">
                    <div class="auth-header">
                        <h1>Varta</h1>
                        <p>Modern Chat Application</p>
                    </div>
                    <div class="alert alert-error" id="auth-error"></div>
                    
                    <!-- Auth Tabs -->
                    <div class="auth-tabs">
                        <ul class="tab-header">
                            <li class="tab-link active" data-tab="login">Login</li>
                            <li class="tab-link" data-tab="signup">Sign Up</li>
                        </ul>

                        <!-- Login Tab -->
                        <div id="login" class="tab-content active">
                            <form id="login-form">
                                <div class="form-group">
                                    <label for="login_username">Username</label>
                                    <input type="text" id="login_username" name="username" required>
                                </div>
                                <div class="form-group">
                                    <label for="login_password">Password</label>
                                    <input type="password" id="login_password" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="login_totp">One-Time Password (OTP)</label>
                                    <input type="text" id="login_totp" name="totp" placeholder="000000" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                        </div>

                        <!-- Signup Tab -->
                        <div id="signup" class="tab-content">
                            <form id="signup-form">
                                <div class="form-group">
                                    <label for="signup_username">Username</label>
                                    <input type="text" id="signup_username" name="username" required>
                                </div>
                                <div class="form-group">
                                    <label for="signup_email">Email</label>
                                    <input type="email" id="signup_email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="signup_firstname">First Name</label>
                                    <input type="text" id="signup_firstname" name="first_name" required>
                                </div>
                                <div class="form-group">
                                    <label for="signup_password">Password</label>
                                    <input type="password" id="signup_password" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="signup_confirm_password">Confirm Password</label>
                                    <input type="password" id="signup_confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Register</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main application container -->
    <div id="app-container" <?php echo $isAuthenticated ? '' : 'style="display: none;"'; ?>>
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <!-- Header with user info -->
                <div class="sidebar-header">
                    <div class="user-profile" id="user-profile">
                        <img src="/public/images/avatar-default.png" alt="Avatar" class="avatar">
                        <div class="user-info">
                            <h3 id="username-display">Loading...</h3>
                            <span class="status online" id="status-display">online</span>
                        </div>
                    </div>
                    <div class="header-actions">
                        <button class="btn-icon" id="search-btn" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn-icon" id="menu-btn" title="Menu">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>

                <!-- Tab navigation -->
                <div class="sidebar-tabs">
                    <button class="tab-btn active" data-tab="messages" title="Messages">
                        <i class="fas fa-comments"></i>
                        <span class="badge" id="unread-badge" style="display: none;">0</span>
                    </button>
                    <button class="tab-btn" data-tab="contacts" title="Contacts">
                        <i class="fas fa-users"></i>
                    </button>
                    <button class="tab-btn" data-tab="groups" title="Groups">
                        <i class="fas fa-layer-group"></i>
                    </button>
                    <button class="tab-btn" data-tab="settings" title="Settings">
                        <i class="fas fa-cog"></i>
                    </button>
                </div>

                <!-- Sidebar content -->
                <div class="sidebar-content">
                    <!-- Messages List -->
                    <div id="messages-tab" class="tab-pane active">
                        <div class="search-box">
                            <input type="text" id="message-search" placeholder="Search conversations..." class="search-input">
                        </div>
                        <div id="messages-list" class="conversation-list">
                            <div class="loading">Loading conversations...</div>
                        </div>
                        <div class="pagination-controls" id="messages-pagination"></div>
                    </div>

                    <!-- Contacts List -->
                    <div id="contacts-tab" class="tab-pane" style="display: none;">
                        <div class="search-box">
                            <input type="text" id="contact-search" placeholder="Search contacts..." class="search-input">
                        </div>
                        <div id="contacts-list" class="contacts-list">
                            <div class="loading">Loading contacts...</div>
                        </div>
                        <div class="pagination-controls" id="contacts-pagination"></div>
                    </div>

                    <!-- Groups List -->
                    <div id="groups-tab" class="tab-pane" style="display: none;">
                        <div class="action-buttons">
                            <button class="btn btn-primary w-100" id="create-group-btn">
                                <i class="fas fa-plus"></i> New Group
                            </button>
                        </div>
                        <div id="groups-list" class="groups-list">
                            <div class="loading">Loading groups...</div>
                        </div>
                        <div class="pagination-controls" id="groups-pagination"></div>
                    </div>

                    <!-- Settings -->
                    <div id="settings-tab" class="tab-pane" style="display: none;">
                        <div class="settings-menu">
                            <button class="settings-item" id="profile-settings-btn">
                                <i class="fas fa-user"></i> Profile
                            </button>
                            <button class="settings-item" id="privacy-settings-btn">
                                <i class="fas fa-lock"></i> Privacy
                            </button>
                            <button class="settings-item" id="notifications-settings-btn">
                                <i class="fas fa-bell"></i> Notifications
                            </button>
                            <hr>
                            <button class="settings-item danger" id="logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main chat area -->
            <main class="chat-area">
                <!-- Chat header -->
                <div class="chat-header" id="chat-header">
                    <div class="chat-info">
                        <h2 id="chat-title">Select a conversation</h2>
                        <span id="chat-status" class="chat-status">Ready</span>
                    </div>
                    <div class="chat-actions">
                        <button class="btn-icon" id="call-btn" title="Call" style="display: none;">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="btn-icon" id="video-btn" title="Video Call" style="display: none;">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="btn-icon" id="info-btn" title="Info" style="display: none;">
                            <i class="fas fa-info-circle"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages container -->
                <div class="messages-container" id="messages-container">
                    <div class="empty-state">
                        <i class="fas fa-comment-dots"></i>
                        <h3>Welcome to Varta</h3>
                        <p>Select a conversation to start messaging</p>
                    </div>
                </div>

                <!-- Typing indicator -->
                <div class="typing-indicator" id="typing-indicator" style="display: none;">
                    <span>Someone is typing</span>
                    <div class="dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <!-- Message input area -->
                <div class="message-input-area" id="message-input-area">
                    <form id="message-form" style="display: none;">
                        <div class="input-group">
                            <button type="button" class="btn-icon" id="attach-btn" title="Attach file">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <input type="text" id="message-input" placeholder="Type a message..." class="message-input">
                            <button type="button" class="btn-icon" id="emoji-btn" title="Emoji">
                                <i class="fas fa-smile"></i>
                            </button>
                            <button type="submit" class="btn-icon" id="send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Loading spinner -->
    <div class="spinner" id="spinner">
        <div class="spinner-circle"></div>
    </div>

    <!-- Modals -->
    <div id="modal-overlay" class="modal-overlay"></div>
    <div id="modals-container"></div>

    <script src="/public/js/spa.js"></script>
    <script src="/public/js/auth.js"></script>
    <script src="/public/js/api-client.js"></script>
    <script src="/public/js/chat.js"></script>
    <script src="/public/js/router.js"></script>
</body>
</html>
