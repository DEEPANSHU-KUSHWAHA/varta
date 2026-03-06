<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VartaSphere - Tactical Nexus</title>
    <link rel="stylesheet" href="/public/css/theme.css">
    <link rel="stylesheet" href="/css/reset.css">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- fonts provided by theme.css -->
</head>
<body class="dark-mode">
    <div class="varta-container">
        <!-- 🎨 Header/Logo Bar -->
        <header class="varta-header">
            <div class="logo-section">
                <div class="logo-badge">
                    <i class="fas fa-satellite"></i>
                    <span class="logo-text">VartaSphere</span>
                    <span class="beta-tag">NEXUS</span>
                </div>
                <span class="protocol-status">● Online</span>
            </div>

            <div class="header-actions">
                <button class="btn-icon search-toggle" title="Search">
                    <i class="fas fa-search"></i>
                </button>
                <button class="btn-icon notifications-toggle" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <button class="btn-icon theme-toggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-dropdown">
                    <button class="btn-user">
                        <img src="/uploads/avatars/default.png" alt="Avatar" class="user-avatar">
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="/public/profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="#" class="dropdown-item" data-action="settings">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <a href="#" class="dropdown-item" data-action="2fa">
                            <i class="fas fa-shield-alt"></i> Two-Factor Auth
                        </a>
                        <hr class="dropdown-divider">
                        <a href="/api/logout.php" class="dropdown-item danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="varta-main">
            <!-- 🎯 Sidebar Navigation -->
            <aside class="varta-sidebar">
                <nav class="sidebar-nav">
                    <div class="nav-section">
                        <h3 class="section-title">PRIMARY STAGE</h3>
                        <a href="#" class="nav-item active" data-section="chat">
                            <i class="fas fa-comments"></i>
                            <span>Active Nodes</span>
                            <span class="badge">12</span>
                        </a>
                        <a href="#" class="nav-item" data-section="groups">
                            <i class="fas fa-users"></i>
                            <span>Communities</span>
                            <span class="badge">5</span>
                        </a>
                        <a href="#" class="nav-item" data-section="canvas">
                            <i class="fas fa-paint-brush"></i>
                            <span>Paint Studio</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h3 class="section-title">AI TACTICAL HUB</h3>
                        <a href="#" class="nav-item" data-section="ai-analysis">
                            <i class="fas fa-brain"></i>
                            <span>Neural Link</span>
                        </a>
                        <a href="#" class="nav-item" data-section="analytics">
                            <i class="fas fa-chart-line"></i>
                            <span>Mesh Stability</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <h3 class="section-title">SETTINGS</h3>
                        <a href="#" class="nav-item" data-section="network">
                            <i class="fas fa-network-wired"></i>
                            <span>Network Config</span>
                        </a>
                    </div>
                </nav>

                <div class="sidebar-footer">
                    <div class="status-indicator">
                        <span class="status-dot online"></span>
                        <span class="status-text">Secure Link Active</span>
                    </div>
                </div>
            </aside>

            <!-- 📱 Main Content Area -->
            <main class="varta-content">
                <!-- Search Panel -->
                <div class="search-panel hidden">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search users, messages, groups..." class="search-input">
                    </div>
                </div>

                <!-- Active Nodes Section -->
                <section id="chat-section" class="content-section active">
                    <div class="section-header">
                        <h2>Active Nodes</h2>
                        <div class="header-tools">
                            <button class="btn-icon" title="Add contact">
                                <i class="fas fa-user-plus"></i>
                            </button>
                            <button class="btn-icon" title="Filter">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>

                    <div class="nodes-grid">
                        <!-- Node Card Template -->
                        <div class="node-card glassmorphism">
                            <div class="node-header">
                                <img src="/uploads/avatars/default.png" alt="User" class="node-avatar">
                                <span class="status-indicator online"></span>
                            </div>
                            <h3 class="node-name">User Name</h3>
                            <p class="node-status">@username</p>
                            <p class="node-bio">Short bio or status message...</p>
                            <div class="node-actions">
                                <button class="btn btn-primary-sm">
                                    <i class="fas fa-envelope"></i> Message
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Chat Section -->
                <section id="messages-section" class="content-section hidden">
                    <div class="chat-container">
                        <div class="chat-header">
                            <h2>Messaging Hub</h2>
                        </div>
                        <div class="chat-messages" id="messagesList">
                            <!-- Messages will load here -->
                        </div>
                        <div class="chat-input-area">
                            <input type="text" placeholder="Type message..." class="chat-input">
                            <button class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Groups Section -->
                <section id="groups-section" class="content-section hidden">
                    <div class="section-header">
                        <h2>Communities</h2>
                        <button class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Group
                        </button>
                    </div>
                    <div class="groups-list" id="groupsList">
                        <!-- Groups will load here -->
                    </div>
                </section>

                <!-- Canvas/Paint Studio -->
                <section id="canvas-section" class="content-section hidden">
                    <div class="section-header">
                        <h2>Paint Studio</h2>
                        <div class="canvas-tools">
                            <button class="btn-icon" title="Brush">
                                <i class="fas fa-paintbrush"></i>
                            </button>
                            <button class="btn-icon" title="Eraser">
                                <i class="fas fa-eraser"></i>
                            </button>
                            <button class="btn-icon" title="Clear">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                    <canvas id="paintCanvas" class="paint-canvas"></canvas>
                </section>
            </main>

            <!-- 📊 AI Tactical Hub (Right Sidebar) -->
            <aside class="varta-tactical-hub">
                <div class="hub-header">
                    <h3>AI TACTICAL HUB</h3>
                    <i class="fas fa-brain pulse"></i>
                </div>

                <div class="hub-section">
                    <h4>Conversation Health</h4>
                    <div class="health-meter">
                        <div class="health-bar" style="width: 75%"></div>
                    </div>
                    <p class="health-text">Healthy • 75%</p>
                </div>

                <div class="hub-section">
                    <h4>Mesh Stability</h4>
                    <div class="stability-grid">
                        <div class="stability-item">
                            <span class="stability-label">Latency</span>
                            <span class="stability-value">24ms</span>
                        </div>
                        <div class="stability-item">
                            <span class="stability-label">Uptime</span>
                            <span class="stability-value">99.9%</span>
                        </div>
                        <div class="stability-item">
                            <span class="stability-label">Nodes</span>
                            <span class="stability-value">12</span>
                        </div>
                    </div>
                </div>

                <div class="hub-section">
                    <h4>Neural Link Suggestions</h4>
                    <div class="suggestions-list">
                        <div class="suggestion-item">
                            <i class="fas fa-lightbulb"></i>
                            <p>Consider grouping by project for better organization</p>
                        </div>
                        <div class="suggestion-item">
                            <i class="fas fa-lightbulb"></i>
                            <p>You have 3 unread messages from Team Lead</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <!-- 📍 Footer - System Telemetry -->
        <footer class="varta-footer">
            <div class="footer-section">
                <span class="footer-item">
                    <i class="fas fa-router"></i>
                    Protocol: v2.1.0
                </span>
                <span class="footer-item">
                    <i class="fas fa-globe"></i>
                    Region: US-EAST
                </span>
                <span class="footer-item">
                    <i class="fas fa-clock"></i>
                    <span id="latency">0ms</span>
                </span>
            </div>
        </footer>
    </div>

    <!-- Modals -->
    <div id="modal-2fa" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Two-Factor Authentication</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="totp-status"></div>
                <div id="totp-setup" class="hidden">
                    <h3>Setup Two-Factor Authentication</h3>
                    <p>Scan this QR code with your authenticator app:</p>
                    <div id="qr-code-container">
                        <img id="qr-code" src="" alt="QR Code">
                    </div>
                    <p>Or enter this code manually:</p>
                    <div class="secret-key">
                        <code id="secret-key"></code>
                        <button class="btn-copy" title="Copy"><i class="fas fa-copy"></i></button>
                    </div>
                    <input type="text" id="totp-verify-code" placeholder="Enter 6-digit code" maxlength="6">
                    <button class="btn btn-primary" id="btn-verify-totp">Verify & Enable</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/dashboard.js"></script>
</body>
</html>
