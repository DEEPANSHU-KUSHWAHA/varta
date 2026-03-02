# Varta SPA - Deployment Guide

## Quick Start Deployment

This guide walks you through deploying Varta to a production environment.

## Prerequisites

- Server with PHP 8.0+
- MySQL 5.7+ or MariaDB
- Apache with mod_rewrite enabled
- Domain name (with SSL/TLS certificate)
- FTP/SFTP access or SSH access
- Composer installed

## Deployment Steps

### 1. Prepare Server Environment

```bash
# SSH into server
ssh user@varta-n.unaux.com

# Check PHP version
php -v

# Check MySQL status
mysql --version

# Verify mod_rewrite is enabled
apache2ctl -M | grep rewrite
# Should show: rewrite_module (shared)

# Create application directory
mkdir -p /var/www/varta
cd /var/www/varta

# Set permissions
sudo chown -R www-data:www-data /var/www/varta
sudo chmod -R 755 /var/www/varta
```

### 2. Clone/Upload Application

**Option A: Using Git**

```bash
cd /var/www/varta
git clone <repository-url> .
git checkout main
```

**Option B: Using SFTP**

```bash
# From local machine
sftp user@varta-n.unaux.com
cd /var/www/varta
put -R /path/to/local/Varta/* .
```

### 3. Install Dependencies

```bash
cd /var/www/varta
composer install --no-dev

# Verify installations
ls vendor/firebase/
ls vendor/phpgangsta/
```

### 4. Configure Environment

#### Database Configuration

Edit `/resources/db.php`:

```php
<?php
// Production database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'varta_user');
define('DB_PASS', 'secure_password_here');
define('DB_NAME', 'varta_db');

// ... rest of configuration
```

#### Create Database & User

```bash
# SSH into server
mysql -u root -p

# In MySQL:
CREATE DATABASE varta_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'varta_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON varta_db.* TO 'varta_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u varta_user -p varta_db < /var/www/varta/database/schema.sql
```

### 5. Configure Apache

Create Virtual Host configuration:

**File**: `/etc/apache2/sites-available/varta.conf`

```apache
<VirtualHost *:80>
    ServerName varta-n.unaux.com
    ServerAlias www.varta-n.unaux.com
    Redirect permanent / https://varta-n.unaux.com/

    DocumentRoot /var/www/varta/public
</VirtualHost>

<VirtualHost *:443>
    ServerName varta-n.unaux.com
    ServerAlias www.varta-n.unaux.com

    DocumentRoot /var/www/varta/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/varta-n.unaux.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/varta-n.unaux.com/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/varta-n.unaux.com/chain.pem

    # HSTS Header
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # Enable mod_rewrite
    <Directory /var/www/varta/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted

        # Ensure .htaccess is processed
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteBase /
        </IfModule>
    </Directory>

    # PHP-FPM Configuration
    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost"
    </FilesMatch>

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/varta-error.log
    CustomLog ${APACHE_LOG_DIR}/varta-access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite varta
sudo a2enmod ssl
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod proxy
sudo a2enmod proxy_fcgi

# Test Apache configuration
sudo apache2ctl configtest
# Should output: Syntax OK

# Restart Apache
sudo systemctl restart apache2
```

### 6. SSL Certificate Setup

Using Let's Encrypt with Certbot:

```bash
sudo apt-get install certbot python3-certbot-apache

# Generate certificate
sudo certbot certonly --apache -d varta-n.unaux.com -d www.varta-n.unaux.com

# Auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer

# Test renewal
sudo certbot renew --dry-run
```

### 7. Configure Session & Upload Directories

```bash
# Create session directory
sudo mkdir -p /var/lib/varta-sessions
sudo chown www-data:www-data /var/lib/varta-sessions
sudo chmod 700 /var/lib/varta-sessions

# Configure PHP to use custom session directory
# Edit /etc/php/8.1/fpm/php.ini
sudo nano /etc/php/8.1/fpm/php.ini

# Find line: session.save_path = "/var/lib/php/sessions"
# Change to: session.save_path = "/var/lib/varta-sessions"

# Create uploads directory
sudo mkdir -p /var/www/varta/uploads
sudo chown www-data:www-data /var/www/varta/uploads
sudo chmod 750 /var/www/varta/uploads
```

### 8. Configure PHP-FPM

Edit `/etc/php/8.1/fpm/pool.d/www.conf`:

```ini
; PHP-FPM Pool Configuration

[www]
user = www-data
group = www-data
listen = /run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data

; Performance tuning
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 2
pm.max_spare_servers = 10

; Timeouts
request_terminate_timeout = 30s
request_slowlog_timeout = 5s
slowlog = /var/log/php-fpm/www-slow.log
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.1-fpm
```

### 9. Configure Firewall

```bash
# Allow HTTP/HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 10. Environment Security

```bash
# Restrict access to sensitive files
cd /var/www/varta

# Create .htaccess to protect sensitive directories
cat > .htaccess << 'EOF'
<FilesMatch "\.(php|sql|git)$">
    Deny from all
</FilesMatch>

<DirectoryMatch "^/\.(git|env)">
    Deny from all
</DirectoryMatch>
EOF

# Protect vendor directory
sudo find /var/www/varta/vendor -type d -exec chmod 755 {} \;
sudo find /var/www/varta/vendor -type f -exec chmod 644 {} \;

# Protect resources
sudo chmod 750 /var/www/varta/resources
```

### 11. Backup Strategy

```bash
# Create backup directory
sudo mkdir -p /var/backups/varta
sudo chown root:root /var/backups/varta
sudo chmod 700 /var/backups/varta

# Create backup script
sudo nano /usr/local/bin/backup-varta.sh
```

**File**: `/usr/local/bin/backup-varta.sh`

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/varta"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup database
mysqldump -u varta_user -p'secure_password' varta_db | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup application
tar czf $BACKUP_DIR/app_$DATE.tar.gz /var/www/varta

# Keep only last 7 days of backups
find $BACKUP_DIR -mtime +7 -delete

echo "Backup completed: $DATE"
```

Make executable and schedule with cron:

```bash
sudo chmod +x /usr/local/bin/backup-varta.sh

# Edit crontab
sudo crontab -e

# Add daily backup at 2 AM
0 2 * * * /usr/local/bin/backup-varta.sh
```

### 12. Monitoring & Logging

#### Configure Application Logging

Create `/var/log/varta/` directory:

```bash
sudo mkdir -p /var/log/varta
sudo chown www-data:www-data /var/log/varta
sudo chmod 755 /var/log/varta
```

#### Monitor Disk Usage

```bash
df -h /var/www/varta
du -sh /var/www/varta/*
```

#### Check Apache Logs

```bash
# Real-time access logs
tail -f /var/log/apache2/varta-access.log

# Real-time error logs
tail -f /var/log/apache2/varta-error.log

# Check for 5xx errors
grep " 5[0-9][0-9] " /var/log/apache2/varta-access.log
```

### 13. Performance Optimization

#### Enable Caching

Add to `/var/www/varta/public/.htaccess`:

```apache
# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
```

#### Enable Gzip Compression

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### 14. Health Checks

```bash
# Test API endpoint
curl -s https://varta-n.unaux.com/api/v1/auth.php | curl -s grep "error"

# Test SPA loads
curl -s https://varta-n.unaux.com/ | grep -q "Varta" && echo "OK" || echo "FAILED"

# Test database connection
curl -s https://varta-n.unaux.com/api/v1/auth.php?action=register \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"username":"test","email":"test@test.com","password":"Test123!"}'
```

### 15. Deployment Verification

```bash
# Verify all components
echo "1. Checking PHP..."
php -v | head -1

echo "2. Checking MySQL..."
mysqladmin -u varta_user -p ping

echo "3. Checking Apache..."
apache2ctl -v | head -1

echo "4. Checking SSL..."
openssl s_client -connect varta-n.unaux.com:443 < /dev/null

echo "5. Checking application..."
curl -s https://varta-n.unaux.com/ | grep -c "Varta" && echo "Application loaded"

echo "All checks passed!"
```

## Rollback Procedure

If deployment fails:

```bash
# Stop the application
sudo systemctl stop apache2

# Restore from backup
cd /var/www/varta
sudo tar xzf /var/backups/varta/app_BACKUP_DATE.tar.gz

# Restore database
mysql -u varta_user -p varta_db < /var/backups/varta/db_BACKUP_DATE.sql

# Start the application
sudo systemctl start apache2
```

## Maintenance

### Regular Tasks

- [ ] Monitor error logs daily
- [ ] Check disk space weekly
- [ ] Test backups weekly
- [ ] Update dependencies monthly
- [ ] Review security patches monthly
- [ ] Update SSL certificates 30 days before expiry

### Scheduled Tasks

```bash
# View cron jobs
crontab -l

# Common maintenance tasks to add:
# Daily backup (2 AM)
0 2 * * * /usr/local/bin/backup-varta.sh

# Weekly logs cleanup (Sunday 3 AM)
0 3 * * 0 find /var/log/varta -mtime +30 -delete

# Database optimization (Monthly, 1st day, 4 AM)
0 4 1 * * mysqladmin -u varta_user -p OPTIMIZE varta_db
```

## Troubleshooting

### 404 Errors in App

```bash
# Verify .htaccess is enabled
grep "AllowOverride All" /etc/apache2/sites-available/varta.conf

# Check rewrite module
apache2ctl -M | grep rewrite

# Test url rewriting
curl -I https://varta-n.unaux.com/
curl -I https://varta-n.unaux.com/api/v1/auth.php
```

### Database Connection Errors

```bash
# Test connection
mysql -u varta_user -p -e "SELECT 1;"

# Check table count
mysql -u varta_user -p -e "SHOW TABLES;" varta_db
```

### Performance Issues

```bash
# Check Apache status
apachectl status

# Check PHP-FPM status
systemctl status php8.1-fpm

# Monitor real-time
top -u www-data
```

## Support

For deployment issues:
- Check error logs: `/var/log/apache2/varta-error.log`
- Check application logs: `/var/log/varta/`
- Review configuration: `/var/www/varta/.htaccess`

---

**Varta v1.0 Deployment Guide** - Last Updated: 2024
