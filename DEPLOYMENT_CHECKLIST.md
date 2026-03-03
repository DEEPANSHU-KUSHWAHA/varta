# Varta Deployment & Maintenance Checklist

## Pre-Deployment

### Code & Repository
- [ ] All code committed to git: `git status` shows clean working directory
- [ ] `.env` file is in `.gitignore`
- [ ] No credentials or secrets in codebase
- [ ] Code follows PSR-12 standards
- [ ] All deprecated code removed
- [ ] API documentation updated

### Dependencies
- [ ] `composer.json` is up to date
- [ ] Run `composer validate`
- [ ] Run `composer audit` for security vulnerabilities
- [ ] All required extensions listed in composer.json

### Database
- [ ] Database backup created
- [ ] Schema version documented
- [ ] Migration scripts tested on copy
- [ ] ALTER TABLE queries ready (`/database/ALTER_TABLES.sql`)
- [ ] Rollback plan in place

### Files & Permissions
- [ ] All necessary files included in deployment package
- [ ] `.env.example` committed to repository
- [ ] `uploads/` directory exists and empty
- [ ] `.gitignore` configured properly

## Deployment

### Environment Setup

```bash
# 1. Clone or deploy project
git clone https://github.com/user/varta.git
cd varta

# 2. Install dependencies
composer install --no-dev

# 3. Copy environment template
cp .env.example .env

# 4. Configure .env with production values
nano .env
# Update:
# - CPANEL_DB_HOST
# - CPANEL_DB_USER
# - CPANEL_DB_PASS
# - CPANEL_DB_NAME
# - JWT_SECRET (generate: openssl rand -base64 32)
# - TOTP_ENC_KEY (generate: openssl rand -hex 32)

# 5. Set file permissions
chmod 755 public
chmod 755 app
chmod 755 api
chmod 755 resources
chmod 755 uploads
mkdir -p uploads/avatars
chmod 755 uploads/avatars
```

### Database Setup

```bash
# Using MySQL CLI
mysql -u varta_user -p varta_db < database/schema.sql

# Apply schema updates (IMPORTANT)
mysql -u varta_user -p varta_db < database/ALTER_TABLES.sql
```

### Verification

```bash
# Check database integrity
php check-db-integrity.php

# Expected output: All checks pass

# Check health status (via browser)
curl https://your-domain/health-check.php
```

## Post-Deployment

### Functional Tests
- [ ] Login page loads: `https://your-domain/login-page.php`
- [ ] Signup page works: `https://your-domain/signup-page.php`
- [ ] Can create account with 2FA
- [ ] Can login with correct credentials
- [ ] Can access dashboard
- [ ] Can send messages
- [ ] Can create groups
- [ ] Notifications work

### API Tests
```bash
# Test login endpoint
curl -X POST https://your-domain/api/v1/auth.php?action=login \
  -d '{"email":"test@example.com", "password":"password"}' \
  -H "Content-Type: application/json"

# Expected: 401 if credentials invalid, 200 if valid with token
```

### Security Tests
- [ ] HTTPS is enforced
- [ ] Security headers set (CSP, X-Frame-Options, etc.)
- [ ] SQL injection attempts blocked
- [ ] XSS attempts blocked
- [ ] CSRF protection working
- [ ] Session hijacking prevention in place

### Performance Tests
- [ ] Page load time < 3 seconds
- [ ] API response time < 500ms
- [ ] Database queries optimized (check slow query log)
- [ ] No memory leaks in long-running processes

## Monitoring & Maintenance

### Daily Checks
- [ ] Application health: `/health-check.php`
- [ ] Error logs reviewed
- [ ] No unhandled exceptions
- [ ] Database connection stable

### Weekly Checks
- [ ] Database backup completed
- [ ] User count and storage trends
- [ ] API error rates acceptable
- [ ] Session data cleaned up

### Monthly Checks
- [ ] Security patches applied
- [ ] Dependency updates available
- [ ] Database optimization (OPTIMIZE TABLE)
- [ ] User feedback reviewed

### Quarterly Checks
- [ ] Full security audit
- [ ] Performance optimization
- [ ] API usage analysis
- [ ] Capacity planning for next quarter

## Troubleshooting

### Database Issues

**Connection Failed**
```bash
# Check credentials in .env
cat .env | grep CPANEL_DB

# Test connection
mysql -h $CPANEL_DB_HOST -u $CPANEL_DB_USER -p$CPANEL_DB_PASS $CPANEL_DB_NAME

# Check MySQL status (via cPanel)
# Services > MySQL > Status
```

**Corrupted Tables**
```sql
-- Check table integrity
CHECK TABLE users;

-- Repair if needed
REPAIR TABLE users;

-- Optimize after repair
OPTIMIZE TABLE users;
```

**Slow Queries**
```sql
-- Enable slow query log (5 second threshold)
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 5;

-- Check log
SHOW VARIABLES LIKE 'slow_query_log%';

-- Analyze query
EXPLAIN SELECT * FROM messages WHERE user_id = 1;

-- Add index if needed
ALTER TABLE messages ADD INDEX idx_user_id (user_id);
```

### Application Issues

**Composer Errors**
```bash
# Clear cache
composer clear-cache

# Reinstall dependencies
rm composer.lock
composer install

# Check autoloader
composer dump-autoload -o
```

**Permission Errors**
```bash
# Fix directory permissions
chmod 755 uploads
chmod 755 public

# Fix file permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
```

**Session Issues**
```bash
# Clear sessions
rm /tmp/sess_*

# Check session handler
php -r "echo ini_get('session.save_handler');"

# Verify session path is writable
ls -la /tmp/ | grep sess
```

### Git Issues

**Push Fails**
```bash
# Check remote
git remote -v

# Update SSH keys
ssh-keygen -t rsa -b 4096 -f ~/.ssh/id_rsa

# Add to GitHub SSH keys

# Test connection
ssh -T git@github.com

# Retry push
git push origin main
```

**Merge Conflicts**
```bash
# View conflicts
git status

# View conflict details
git diff

# Resolve conflicts manually

# Stage resolved files
git add .

# Complete merge
git commit -m "Resolved merge conflicts"
```

## Rollback Procedures

### Code Rollback
```bash
# View history
git log --oneline -10

# Revert to previous version
git revert <commit-hash>
git push origin main

# Or reset (use with caution)
git reset --hard <commit-hash>
git push origin main -f
```

### Database Rollback
```bash
# From backup
mysql varta_db < backup/varta_db_2024-03-03.sql

# Using point-in-time recovery (if enabled)
# Contact hosting provider for assistance
```

## Documentation Files

- `README.md` - Project overview and features
- `SETUP_AND_CONFIG.md` - Initial setup guide
- `.env.example` - Environment variables template
- `database/schema.sql` - Database schema
- `database/ALTER_TABLES.sql` - Schema updates
- `public/health-check.php` - Health check endpoint
- `check-db-integrity.php` - Integrity checker script

## Support & Emergency Contacts

**Database Issues**
- Contact hosting provider support
- Provide error messages and logs

**Security Issues**
- Disable affected feature immediately
- Research patch/workaround
- Update dependencies
- Redeploy

**Performance Issues**
- Check slow query log
- Analyze API endpoints
- Review resource usage
- Optimize database queries

## Version History

### Current Version: 1.0.0
- Initial production release
- 2FA authentication (TOTP)
- JWT token management
- Encrypted TOTP storage
- Complete API
- SPA interface

---

**Last Updated**: March 2026  
**Maintained By**: Development Team  
**Contact**: admin@varta.local
