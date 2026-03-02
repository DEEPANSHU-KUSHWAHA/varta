document.addEventListener('DOMContentLoaded', function() {
    initDashboard();
});

function initDashboard() {
    setupNavigation();
    setup2FA();
    setupUserDropdown();
    updateLatency();
    loadActiveNodes();
}

// ============ NAVIGATION ============
function setupNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const section = item.dataset.section;
            
            // Remove active from all items
            navItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(s => {
                s.classList.remove('active');
            });
            
            // Show selected section
            const sectionEl = document.getElementById(`${section}-section`);
            if (sectionEl) {
                sectionEl.classList.add('active');
            }
        });
    });
}

// ============ USER DROPDOWN ============
function setupUserDropdown() {
    const userDropdown = document.querySelector('.user-dropdown');
    const btnUser = document.querySelector('.btn-user');
    
    btnUser.addEventListener('click', () => {
        userDropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!userDropdown.contains(e.target)) {
            userDropdown.classList.remove('active');
        }
    });
}

// ============ TWO-FACTOR AUTHENTICATION ============
function setup2FA() {
    const modal = document.getElementById('modal-2fa');
    const closeBtn = document.querySelector('.modal-close');
    const dropdownItems = document.querySelectorAll('[data-action="2fa"]');
    
    dropdownItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            checkTOTPStatus();
            modal.classList.remove('hidden');
        });
    });
    
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
}

function checkTOTPStatus() {
    fetch('/api/totp_qr.php?action=status')
        .then(r => r.json())
        .then(data => {
            const statusDiv = document.getElementById('totp-status');
            const setupDiv = document.getElementById('totp-setup');
            
            if (data.totp_enabled) {
                statusDiv.innerHTML = `
                    <div style="text-align: center; color: var(--success);">
                        <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                        <h3>Two-Factor Authentication is Enabled ✓</h3>
                        <p>Your account is protected with 2FA.</p>
                        <button class="btn btn-primary" id="btn-disable-2fa" style="margin-top: 1rem;">
                            <i class="fas fa-times"></i> Disable 2FA
                        </button>
                    </div>
                `;
                setupDiv.classList.add('hidden');
                
                const disableBtn = document.getElementById('btn-disable-2fa');
                if (disableBtn) {
                    disableBtn.addEventListener('click', disable2FA);
                }
            } else {
                statusDiv.innerHTML = '';
                setupDiv.classList.remove('hidden');
                generateTOTPCode();
            }
        })
        .catch(err => {
            console.error('Error checking TOTP status:', err);
            alert('Error checking 2FA status');
        });
}

function generateTOTPCode() {
    fetch('/api/totp_qr.php?action=generate')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('qr-code').src = data.qr_code_url;
                document.getElementById('secret-key').textContent = data.secret;
                
                // Setup copy button
                const copyBtn = document.querySelector('.btn-copy');
                if (copyBtn) {
                    copyBtn.addEventListener('click', () => {
                        navigator.clipboard.writeText(data.secret);
                        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                        setTimeout(() => {
                            copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                        }, 2000);
                    });
                }
                
                // Setup verify button
                const verifyBtn = document.getElementById('btn-verify-totp');
                if (verifyBtn) {
                    verifyBtn.addEventListener('click', verifyTOTPCode);
                }
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(err => {
            console.error('Error generating TOTP:', err);
            alert('Error generating QR code');
        });
}

function verifyTOTPCode() {
    const code = document.getElementById('totp-verify-code').value;
    
    if (code.length !== 6 || isNaN(code)) {
        alert('❌ Please enter a valid 6-digit code');
        return;
    }
    
    const formData = new FormData();
    formData.append('code', code);
    
    fetch('/api/totp_qr.php?action=verify', {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✓ Two-Factor Authentication enabled!');
                document.getElementById('modal-2fa').classList.add('hidden');
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert('✗ ' + data.message);
                document.getElementById('totp-verify-code').value = '';
            }
        })
        .catch(err => {
            console.error('Error verifying TOTP:', err);
            alert('Error verifying code');
        });
}

function disable2FA() {
    const password = prompt('Enter your password to disable 2FA:');
    if (!password) return;
    
    const formData = new FormData();
    formData.append('password', password);
    
    fetch('/api/totp_qr.php?action=disable', {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✓ Two-Factor Authentication disabled');
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert('✗ ' + data.message);
            }
        })
        .catch(err => {
            console.error('Error disabling 2FA:', err);
            alert('Error disabling 2FA');
        });
}

// ============ LOAD ACTIVE NODES ============
function loadActiveNodes() {
    fetch('/api/v1/users.php?action=contacts')
        .then(r => r.json())
        .then(data => {
            const grid = document.getElementById('nodesGrid');
            
            if (data.success && data.data && data.data.length > 0) {
                grid.innerHTML = '';
                
                data.data.forEach(user => {
                    const card = document.createElement('div');
                    card.className = 'node-card glassmorphism';
                    card.innerHTML = `
                        <div class="node-header">
                            <img src="${user.avatar_path || '/uploads/avatars/default.png'}" alt="${user.username}" class="node-avatar">
                            <span class="status-indicator ${user.status || 'offline'}" style="position: absolute; bottom: 10px; right: 10px; width: 12px; height: 12px; border-radius: 50%; background: ${user.status === 'online' ? '#00ff88' : '#888888'};"></span>
                        </div>
                        <h3 class="node-name">${user.first_name || user.username} ${user.last_name || ''}</h3>
                        <p class="node-status">@${user.username}</p>
                        <p class="node-bio">Ready to chat</p>
                        <div class="node-actions">
                            <button class="btn btn-primary-sm" onclick="startChat(${user.contact_id})">
                                <i class="fas fa-envelope"></i> Message
                            </button>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            } else {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
                        <p>No contacts yet. Start by adding friends!</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error loading contacts:', err);
            const grid = document.getElementById('nodesGrid');
            grid.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--danger);">
                    <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    <p>Error loading contacts</p>
                </div>
            `;
        });
}

function startChat(userId) {
    console.log('Starting chat with user:', userId);
    alert('Chat feature coming soon!');
}

// ============ LATENCY UPDATE ============
function updateLatency() {
    setInterval(() => {
        const start = performance.now();
        
        fetch('/api/v1/users.php?action=profile', {
            method: 'GET'
        })
            .then(() => {
                const latency = Math.round(performance.now() - start);
                const latencyEl = document.getElementById('latency');
                if (latencyEl) {
                    latencyEl.textContent = latency + 'ms';
                }
            })
            .catch(err => {
                console.error('Latency check failed:', err);
            });
    }, 5000);
}