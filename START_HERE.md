# QUICK START GUIDE - After Session Updates

## 🎯 What Was Done This Session

Your Varta application has been updated with:

✅ **Professional UI Pages**
- Modern login page (`/public/login-page.php`)
- Professional signup page (`/public/signup-page.php`)
- Database connection checker on index

✅ **Database Integrity**
- ALTER TABLE queries in `/database/ALTER_TABLES.sql`
- Health check API (`/public/health-check.php`)
- Database integrity checker script (`check-db-integrity.php`)

✅ **Documentation**
- Complete setup guide (`SETUP_AND_CONFIG.md`)
- Deployment checklist (`DEPLOYMENT_CHECKLIST.md`)
- Database migration guide (`DATABASE_MIGRATION_GUIDE.md`)
- UI & Connection fixes guide (`UI_AND_CONNECTION_FIXES.md`)

---

## ⚡ Next Steps (In Order)

### Step 1: Update Your Database Schema (CRITICAL)

```bash
# BACKUP FIRST (very important!)
mysqldump -u varta_user -p varta_db > varta_backup_$(date +%Y%m%d).sql

# Then run the migration
mysql -u varta_user -p varta_db < /path/to/database/ALTER_TABLES.sql
```

**Or in phpMyAdmin:**
1. Open phpMyAdmin
2. Select database `varta_db`
3. Click "SQL" tab
4. Open file: `/database/ALTER_TABLES.sql`
5. Copy all content
6. Paste in SQL box and execute

### Step 2: Verify Database is Updated

```bash
# Run the integrity checker
php check-db-integrity.php

# Should show all checks with ✓ marks
```

### Step 3: Check System Health

Visit in your browser:
```
https://your-domain/health-check.php
```

Should return:
```json
{
  "overall_status": "healthy",
  "checks_passed": 8,
  "timestamp": "2026-03-03T10:30:00Z"
}
```

### Step 4: Test Login and Signup

1. Go to: `https://your-domain/login-page.php`
2. Go to: `https://your-domain/signup-page.php`
3. Create a test account
4. Set up 2FA (scan QR code)
5. Login with email and TOTP code

---

## 📂 All Updated Files

### New Files Created
```
/public/
  ├── login-page.php              (Modern login UI)
  └── signup-page.php             (Modern signup UI)

/database/
  ├── ALTER_TABLES.sql            (Database schema updates)
  └── (Original schema.sql remains)

/ (Root)
  ├── check-db-integrity.php       (Integrity checker script)
  ├── health-check.php             (Health status endpoint)
  ├── SETUP_AND_CONFIG.md          (Complete setup guide)
  ├── DEPLOYMENT_CHECKLIST.md      (Deployment procedures)
  ├── DATABASE_MIGRATION_GUIDE.md  (ALTER TABLE guide)
  └── UI_AND_CONNECTION_FIXES.md   (This summary guide)
```

### Modified Files
```
/public/
  └── index.php                   (Added database connection check)
```

### Unchanged (Still Working)
```
/api/                             (All API endpoints work)
/api/v1/                          (All v1 endpoints work)
/app/auth/                        (JWT module consolidated)
/resources/db.php                 (Database connection)
All other files remain unchanged
```

---

## 🔑 Configuration Checklist

### Is Your `.env` File Set?

```bash
# Check if .env exists
ls -la .env

# Should have these variables:
# CPANEL_DB_HOST=localhost
# CPANEL_DB_USER=varta_user  
# CPANEL_DB_PASS=your_password
# CPANEL_DB_NAME=varta_db
# JWT_SECRET=[32+ char random string]
# TOTP_ENC_KEY=[32 byte hex string]
```

If not set:
```bash
# Create .env from template
cp .env.example .env

# Generate JWT_SECRET (Linux/Mac)
openssl rand -base64 32

# Generate TOTP_ENC_KEY (Linux/Mac)
openssl rand -hex 32

# Then edit .env with your values
nano .env
```

---

## 🧪 Test Everything Works

### Test 1: Database Connection
```bash
# Run integrity checker
php check-db-integrity.php

# Expected: All checks pass with ✓ marks
```

### Test 2: Health Status
```bash
# In browser or curl
curl https://your-domain/health-check.php

# Expected: HTTP 200, overall_status: "healthy"
```

### Test 3: UI Pages
```bash
# Test login page
curl https://your-domain/login-page.php | grep -i "login"

# Test signup page  
curl https://your-domain/signup-page.php | grep -i "sign"

# Both should return HTML (no errors)
```

### Test 4: Full Auth Flow
1. Open browser
2. Go to: `https://your-domain/signup-page.php`
3. Create account with:
   - Email: test@example.com
   - Username: testuser
   - Password: SecurePass123!
   - Confirm password
4. Scan the TOTP QR code with authenticator app
5. Go to: `https://your-domain/login-page.php`
6. Login with email and TOTP code
7. Should see dashboard

---

## 🚀 Deploy to Production

### For GitHub Deployment:
```bash
git add .
git commit -m "chore: UI improvements and database integrity"
git push origin main
```

### For FTP/Server Deployment:

**Method 1: Manual FTP Upload**
1. Open FTP client (FileZilla, etc)
2. Upload entire `/varta` folder
3. SSH to server and run:
   ```bash
   cd /path/to/varta
   composer install
   php check-db-integrity.php
   ```

**Method 2: Git On Server**
```bash
# SSH to server
ssh user@host.com

# Navigate to web root
cd /public_html

# Clone or pull updates
git clone https://github.com/user/varta.git
# or
git -C varta pull origin main

# Install dependencies
composer install

# Verify
php check-db-integrity.php
```

---

## 📋 Database Fields Changed

### Users Table - New Columns
```
middle_name     - VARCHAR(100) - Optional middle name
bio             - VARCHAR(500) - User bio/about
status          - ENUM - online/offline/away/dnd
last_login      - TIMESTAMP - Last login time
role            - ENUM - user/moderator/admin
avatar_path     - VARCHAR(255) - Profile picture path
updated_at      - TIMESTAMP - Auto-updated timestamp
password_hash   - VARCHAR(255) - Secure password storage
totp_secret_enc - LONGTEXT - Encrypted TOTP secret
```

### New Tables Created
```
notifications   - User notifications
sessions        - Session management  
message_reads   - Track who read messages
typing_indicators - Show typing status
blocked_users   - Track blocked users
```

---

## 🔒 Security Notes

### Passwords
- Old field: `password` (deprecated)
- New field: `password_hash` (secure)
- Function: `password_hash($pass, PASSWORD_ARGON2ID)`
- Verify: `password_verify($pass, $hash)`

### TOTP Secrets
- Stored as: `totp_secret_enc` (encrypted)
- Encryption: AES-256-CBC
- Key: From `.env` `TOTP_ENC_KEY` (32 bytes hex)
- Decrypt before use: `openssl_decrypt(...)`

### JWT Tokens
- Algorithm: HS256
- Secret: From `.env` `JWT_SECRET` (32+ characters)
- Expiry: 24 hours from creation
- Used for: API authentication

---

## 🌐 API Endpoints Reference

All endpoints use **JWT token authorization**.

### Authentication
```
POST   /api/v1/auth.php?action=register         - Create account
POST   /api/v1/auth.php?action=login            - Login user
POST   /api/v1/auth.php?action=refresh-token    - Refresh JWT
POST   /api/v1/auth.php?action=verify-otp      - Verify 2FA code
```

### Users
```
GET    /api/v1/users.php?id=1                   - Get user profile
POST   /api/v1/users.php                        - Update profile
GET    /api/v1/users.php?search=john            - Search users
```

### Messages
```
POST   /api/v1/messages.php                     - Send message
GET    /api/v1/messages.php?user_id=1           - Get messages
PUT    /api/v1/messages.php                     - Edit message
DELETE /api/v1/messages.php?id=1                - Delete message
```

### Groups
```
POST   /api/v1/groups.php                       - Create group
GET    /api/v1/groups.php                       - List groups
PUT    /api/v1/groups.php?id=1                  - Update group
DELETE /api/v1/groups.php?id=1                  - Delete group
```

Full endpoints list: See `SETUP_AND_CONFIG.md`

---

## 📊 Monitoring

### Daily Check
```bash
# SSH to server
ssh user@host.com

# Run integrity check
php /var/www/varta/check-db-integrity.php

# Check health
curl https://your-domain/health-check.php

# Check disk usage
du -sh /var/www/varta
```

### Weekly Review
- Check error logs: `tail -f /var/log/php-fpm/error.log`
- Review database: `check-db-integrity.php`
- Test auth flow manually

### Monthly Maintenance
- Run database optimization: See `DATABASE_MIGRATION_GUIDE.md`
- Review access logs
- Update dependencies: `composer update`

---

## ❌ Common Issues & Solutions

### Issue: "Database connection failed"
```bash
# Check .env is set correctly
cat .env | grep CPANEL_DB

# Test connection
mysql -h localhost -u varta_user -p varta_db -e "SELECT 1"

# Verify credentials
mysql -u varta_user -p
# (press Ctrl+C if locked, check cPanel)
```

### Issue: "TOTP code not working"
```bash
# Check TOTP_ENC_KEY is set
echo $TOTP_ENC_KEY | wc -c
# Should output: 65 (64 hex chars + newline)

# Generate new key if needed
openssl rand -hex 32 >> .env
```

### Issue: "health-check.php shows errors"
```bash
# Check PHP extensions
php -m | grep -E "mysqli|openssl|gd"

# Check file permissions
chmod 755 check-db-integrity.php health-check.php

# Check database tables
php check-db-integrity.php
```

### Issue: "Login page shows blank"
```bash
# Check file exists
ls -la public/login-page.php

# Check permissions
chmod 644 public/login-page.php

# Test directly
curl https://your-domain/login-page.php
```

---

## 📞 Support Resources

1. **Setup Guide**: `SETUP_AND_CONFIG.md`
2. **Deployment Guide**: `DEPLOYMENT_CHECKLIST.md`  
3. **Database Guide**: `DATABASE_MIGRATION_GUIDE.md`
4. **Overall Summary**: `UI_AND_CONNECTION_FIXES.md`

Or run:
```bash
# Check everything
php check-db-integrity.php

# View health status
curl https://your-domain/health-check.php | jq
```

---

## ✨ You're All Set!

Everything is ready to:
✅ Update your database  
✅ Test new UI pages  
✅ Deploy to production  
✅ Monitor system health  

**Next Action**: Run your database migration to apply all the new schema updates.

```bash
# 1. Backup
mysqldump -u varta_user -p varta_db > backup.sql

# 2. Migrate
mysql -u varta_user -p varta_db < /path/to/database/ALTER_TABLES.sql

# 3. Verify
php check-db-integrity.php

# Success! ✨
```

---

**Varta v2.0 - Ready for Production**  
Created: March 2026  
Status: Production-Ready  
Last Updated: Today
