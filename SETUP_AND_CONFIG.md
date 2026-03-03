# Varta Setup & Configuration Guide

## Quick Start

### 1. Environment Configuration

Copy `.env.example` to `.env` and configure:

```bash
# Database Configuration
CPANEL_DB_HOST=localhost or your-cpanel-host
CPANEL_DB_USER=your_db_user
CPANEL_DB_PASS=your_db_password
CPANEL_DB_NAME=varta_db

# JWT Configuration (generate a strong random key)
JWT_SECRET=your-secret-key-here-min-32-characters

# TOTP Encryption (must be 32 bytes hex encoded)
TOTP_ENC_KEY=0123456789abcdef0123456789abcdef
```

Generate strong keys:

```bash
# Linux/Mac - Generate JWT_SECRET
openssl rand -base64 32

# Generate TOTP_ENC_KEY (32 bytes = 64 hex characters)
openssl rand -hex 32
```

### 2. Database Setup

#### Option A: Using phpMyAdmin (cPanel)

1. Create a new database named `varta_db`
2. Create a new user and assign to database
3. Go to Tools → SQL Import
4. Upload `/database/schema.sql`
5. Execute the queries

#### Option B: Using MySQL Command Line

```sql
-- Create database
CREATE DATABASE varta_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'varta_user'@'localhost' IDENTIFIED BY 'your_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON varta_db.* TO 'varta_user'@'localhost';
FLUSH PRIVILEGES;

-- Select database and import schema
USE varta_db;
SOURCE /path/to/database/schema.sql;

-- Apply schema updates (optional but recommended)
SOURCE /path/to/database/ALTER_TABLES.sql;
```

### 3. Update Table Structure

Run the ALTER TABLE queries to ensure all columns are present:

```bash
# MySQL CLI
mysql -u varta_user -p varta_db < database/ALTER_TABLES.sql
```

Or use phpMyAdmin to execute SQL from `/database/ALTER_TABLES.sql`

### 4. Install Dependencies

```bash
# Install Composer dependencies
composer install

# Verify installation
composer validate
```

### 5. Set File Permissions

```bash
# Linux/Mac
chmod 755 public/
chmod 755 app/
chmod 755 api/
chmod 755 resources/
chmod 755 uploads/
chmod 755 uploads/avatars/ 2>/dev/null || mkdir -p uploads/avatars/ && chmod 755 uploads/avatars/

# Windows (run as Administrator in PowerShell)
icacls "C:\path\to\Varta\uploads" /grant Everyone:(OI)(CI)F
```

### 6. Verify Setup

Access health check at: `http://your-domain/health-check.php`

Expected output:
```json
{
  "overall_status": "healthy",
  "checks_passed": 8,
  "checks_warning": 0,
  "checks_error": 0
}
```

## Connection Management

### Database Connection

The application uses MySQLi for database connections:

**File**: `/resources/db.php`

```php
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    // Connection failed - app will display error
    exit;
}
```

**Testing**: Visit `/health-check.php` and check:
- Database Connection (should be "success")
- Database info and sizes

### FTP Integration

FTP is not directly integrated but you can:

1. **Upload via FTP Client** (FileZilla, WinSCP):
   - Upload entire project to web root
   - Run `composer install` on server after upload

2. **Deploy via GitHub + Webhook**:
   - Push to GitHub repository
   - Configure webhook to pull on push
   - Files auto-sync without FTP

3. **Command Line Deployment**:
   ```bash
   git clone https://github.com/user/varta.git
   cd varta
   composer install
   cp .env.example .env
   # Configure .env file
   mysql varta_db < database/schema.sql
   ```

### GitHub Integration

**Verify Git Status**:
```bash
git status
git log --oneline -5
```

**Check Remote**:
```bash
git remote -v
# Output:
# origin  https://github.com/user/varta.git (fetch)
# origin  https://github.com/user/varta.git (push)
```

**Push Changes**:
```bash
git add .
git commit -m "Fix: UI improvements and database integrity"
git push origin main
```

## API Endpoints

All API endpoints are at `/api/v1/`

### Authentication
- **POST** `/api/v1/auth.php?action=login` - User login
- **POST** `/api/v1/auth.php?action=register` - User registration
- **POST** `/api/v1/auth.php?action=refresh-token` - Refresh JWT token
- **POST** `/api/v1/auth.php?action=verify-otp` - Verify 2FA code (signup/login)
- **POST** `/api/v1/auth.php?action=logout` - User logout

### Users
- **GET** `/api/v1/users.php?action=profile` - Get user profile
- **POST** `/api/v1/users.php?action=profile` - Update profile
- **GET** `/api/v1/users.php?action=search` - Search users
- **GET** `/api/v1/users.php?action=contacts` - Get contacts list

### Messages
- **GET** `/api/v1/messages.php?action=fetch` - Fetch messages
- **POST** `/api/v1/messages.php?action=send` - Send message
- **POST** `/api/v1/messages.php?action=edit` - Edit message
- **POST** `/api/v1/messages.php?action=delete` - Delete message

### Groups
- **GET** `/api/v1/groups.php?action=list` - List groups
- **POST** `/api/v1/groups.php?action=create` - Create group
- **POST** `/api/v1/groups.php?action=join` - Join group
- **POST** `/api/v1/groups.php?action=leave` - Leave group

### Notifications
- **GET** `/api/v1/notifications.php?action=list` - Get notifications
- **POST** `/api/v1/notifications.php?action=mark-read` - Mark as read
- **GET** `/api/v1/notifications.php?action=unread-count` - Unread count

## Authentication Flow

### Two-Factor Authentication (2FA)

1. **During Signup**:
   - User creates account
   - TOTP secret is encrypted and stored
   - QR code generated for authenticator app

2. **During Login**:
   - User enters email & password
   - User enters 6-digit code from authenticator
   - Server verifies and creates JWT token

3. **Token Usage**:
   - JWT sent in Authorization header
   - Stored in localStorage (SPA only)
   - Expires in 24 hours

## Database Schema

### Core Tables

**users**
- id: Primary key
- username: Unique username
- email: Unique email
- password_hash: Bcrypt/Argon2 hashed password
- totp_secret_enc: AES-256 encrypted TOTP secret
- first_name, last_name, middle_name
- phone, bio, avatar_path
- status: online/offline/away
- role: user/moderator/admin
- last_login: Timestamp
- created_at, updated_at: Timestamps

**messages**
- id: Primary key
- sender_id: Foreign key to users
- recipient_id: Foreign key to users (for DMs)
- group_id: Foreign key to groups (for group messages)
- content: Message text
- message_type: text/image/file/audio/video
- is_edited, is_deleted: Boolean flags
- created_at, edited_at: Timestamps

**groups**
- id: Primary key
- name: Group name
- description: Group description
- avatar_path: Group avatar
- creator_id: Foreign key to users
- is_private: Boolean flag
- created_at, updated_at: Timestamps

**contacts**
- id: Primary key
- user_id: Foreign key to users
- contact_id: Foreign key to users
- status: blocked/pending/accepted
- created_at: Timestamp

**notifications**
- id: Primary key
- user_id: Foreign key to users
- message: Notification text
- type: info/success/warning/error/message/group
- is_read: Boolean flag
- created_at: Timestamp

## Security Checklist

- [ ] `.env` file is NOT in version control (use `.gitignore`)
- [ ] Database user has limited privileges (no DROP/CREATE)
- [ ] JWT_SECRET is strong (32+ characters, random)
- [ ] TOTP_ENC_KEY is generated properly (32 bytes hex)
- [ ] HTTPS is enabled in production
- [ ] Session cookies are HttpOnly and Secure
- [ ] SQL injection prevented (using prepared statements)
- [ ] CSRF tokens used in forms
- [ ] Rate limiting on auth endpoints
- [ ] Password minimum 8 characters
- [ ] Regular database backups scheduled

## Troubleshooting

### Database Connection Failed
1. Check `.env` configuration
2. Run `/health-check.php`
3. Verify MySQL is running: `mysql -u root -p`
4. Check database user privileges

### Tables Not Found
1. Run `/database/schema.sql`
2. Run `/database/ALTER_TABLES.sql`
3. Check database name in `.env`

### 2FA Not Working
1. Verify TOTP_ENC_KEY in `.env`
2. Check authenticator app is synchronized with server time
3. Allow 30-second time window for code verification

### File Upload Issues
1. Check `/uploads` directory permissions
2. Verify PHP upload limits in php.ini
3. Check GD extension is installed

### Git Push Fails
1. Check GitHub SSH keys: `ssh -T git@github.com`
2. Verify origin URL: `git remote -v`
3. Pull latest changes: `git pull origin main`

## Performance Tips

1. **Enable Query Caching**: Configure in my.cnf
2. **Add Database Indexes**: Use ALTER_TABLES.sql
3. **Compress Files**: Enable GZIP in Apache/Nginx
4. **Use CDN**: Serve static assets from CDN
5. **Cache API Responses**: Implement Redis caching

## Support & Updates

- **Documentation**: See README.md
- **Issues**: Report on GitHub Issues
- **Contact**: admin@varta.local

---

Version: 1.0  
Last Updated: March 2026
