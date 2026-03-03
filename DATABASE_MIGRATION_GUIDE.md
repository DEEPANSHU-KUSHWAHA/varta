# Database Migration Guide - ALTER TABLE Queries

## 📋 Overview

This guide contains all ALTER TABLE queries needed to update your database schema to the latest version. Run these queries **after** creating the initial database with `schema.sql`.

## ⚠️ Important: Backup First!

**BEFORE running any queries, create a backup:**

```bash
# Create backup copy
mysqldump -u varta_user -p varta_db > varta_db_backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup was created
ls -lh varta_db_backup_*.sql
```

## 🚀 How to Run Queries

### **Method 1: All at Once (Fastest)**
```bash
mysql -u varta_user -p varta_db < /path/to/database/ALTER_TABLES.sql
```

### **Method 2: Line by Line (Safest)**
1. Open phpMyAdmin
2. Select database `varta_db`
3. Click "SQL" tab
4. Paste each query below
5. Click Execute
6. Note the result

### **Method 3: In Terminal One by One**
```bash
mysql -u varta_user -p varta_db << 'EOF'
[paste query here]
EOF
```

---

## 📝 ALTER TABLE Queries

### Query 1: Add Missing Columns to Users Table

**Purpose**: Add user profile fields

```sql
ALTER TABLE users 
ADD COLUMN middle_name VARCHAR(100) DEFAULT NULL AFTER first_name,
ADD COLUMN bio VARCHAR(500) DEFAULT NULL AFTER middle_name,
ADD COLUMN status ENUM('online', 'offline', 'away', 'dnd') DEFAULT 'offline' AFTER bio,
ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER status,
ADD COLUMN role ENUM('user', 'moderator', 'admin') DEFAULT 'user' AFTER last_login,
ADD COLUMN avatar_path VARCHAR(255) DEFAULT NULL AFTER role,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;
```

**What it does:**
- Adds middle name field
- Adds user bio/about section
- Adds online status tracking
- Adds last login tracking
- Adds role-based access control
- Adds avatar path
- Adds automatic update timestamp

**Expected result**: `Query OK, 0 rows affected`

---

### Query 2: Add Performance Indexes to Users Table

**Purpose**: Improve database query speed

```sql
ALTER TABLE users 
ADD INDEX idx_email (email),
ADD INDEX idx_username (username),
ADD INDEX idx_status (status),
ADD INDEX idx_role (role),
ADD INDEX idx_created_at (created_at DESC);
```

**What it does:**
- Speeds up lookups by email
- Speeds up lookups by username
- Speeds up status filtering
- Speeds up role-based queries
- Speeds up date sorting

**Expected result**: `Query OK, 0 rows affected`

---

### Query 3: Ensure Password Hash Column Exists

**Purpose**: Ensure password_hash field for secure storage

```sql
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) DEFAULT NULL;
```

**What it does:**
- Adds password hash storage field
- Only if it doesn't already exist

**Expected result**: `Query OK, 0 rows affected`

---

### Query 4: Ensure TOTP Encryption Column Exists

**Purpose**: Store encrypted TOTP secrets

```sql
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS totp_secret_enc LONGTEXT DEFAULT NULL;
```

**What it does:**
- Adds encrypted TOTP secret storage
- Only if it doesn't already exist
- LONGTEXT for AES-256-CBC encrypted data

**Expected result**: `Query OK, 0 rows affected`

---

### Query 5: Create Notifications Table (if not exists)

**Purpose**: Store user notifications

```sql
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  sender_id INT DEFAULT NULL,
  message VARCHAR(500) NOT NULL,
  type ENUM('message', 'group', 'friend_request', 'system') DEFAULT 'system',
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_is_read (is_read),
  INDEX idx_created_at (created_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**What it does:**
- Stores notifications for users
- Links to sender and recipient
- Tracks read status
- Indexes for fast filtering

**Expected result**: `Query OK, 0 rows affected` (if table already exists)

---

### Query 6: Create Sessions Table (if not exists)

**Purpose**: Store session information

```sql
CREATE TABLE IF NOT EXISTS sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  session_token VARCHAR(255) UNIQUE NOT NULL,
  ip_address VARCHAR(45),
  user_agent TEXT,
  expires_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_session_token (session_token),
  INDEX idx_user_id (user_id),
  INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**What it does:**
- Tracks user sessions
- Stores session tokens
- Records IP and user agent
- Tracks session expiration

**Expected result**: `Query OK, 0 rows affected` (if table already exists)

---

### Query 7: Create Message Reads Table (if not exists)

**Purpose**: Track which users have read which messages

```sql
CREATE TABLE IF NOT EXISTS message_reads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message_id INT NOT NULL,
  user_id INT NOT NULL,
  read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_read (message_id, user_id),
  FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**What it does:**
- Records when users read messages
- One entry per user per message
- Links to messages and users

**Expected result**: `Query OK, 0 rows affected` (if table already exists)

---

### Query 8: Add Indexes to Messages Table

**Purpose**: Improve message query performance

```sql
ALTER TABLE messages 
ADD INDEX IF NOT EXISTS idx_sender_id (sender_id),
ADD INDEX IF NOT EXISTS idx_receiver_id (receiver_id),
ADD INDEX IF NOT EXISTS idx_group_id (group_id),
ADD INDEX IF NOT EXISTS idx_created_at (created_at DESC);
```

**What it does:**
- Speeds up message lookups by sender
- Speeds up message lookups by receiver
- Speeds up group message filtering
- Speeds up chronological sorting

**Expected result**: `Query OK, 0 rows affected`

---

### Query 9: Add Indexes to Groups Table

**Purpose**: Improve group query performance

```sql
ALTER TABLE groups 
ADD INDEX IF NOT EXISTS idx_created_by (created_by),
ADD INDEX IF NOT EXISTS idx_created_at (created_at DESC);
```

**What it does:**
- Speeds up lookups by group creator
- Speeds up chronological sorting

**Expected result**: `Query OK, 0 rows affected`

---

### Query 10: Add Indexes to Contacts Table

**Purpose**: Improve contact query performance

```sql
ALTER TABLE contacts 
ADD INDEX IF NOT EXISTS idx_user_id (user_id),
ADD INDEX IF NOT EXISTS idx_contact_user_id (contact_user_id),
ADD INDEX IF NOT EXISTS idx_created_at (created_at DESC);
```

**What it does:**
- Speeds up contact lookups by user
- Speeds up contact lookups by contact
- Speeds up chronological sorting

**Expected result**: `Query OK, 0 rows affected`

---

### Query 11: Add Indexes to Group_Members Table

**Purpose**: Improve group membership query performance

```sql
ALTER TABLE group_members 
ADD INDEX IF NOT EXISTS idx_group_id (group_id),
ADD INDEX IF NOT EXISTS idx_user_id (user_id),
ADD INDEX IF NOT EXISTS idx_joined_at (joined_at DESC);
```

**What it does:**
- Speeds up lookups by group
- Speeds up lookups by user
- Speeds up chronological sorting

**Expected result**: `Query OK, 0 rows affected`

---

### Query 12: Create Typing Indicators Table (if not exists)

**Purpose**: Show when users are typing

```sql
CREATE TABLE IF NOT EXISTS typing_indicators (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  group_id INT,
  receiver_id INT,
  typing_start_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  typing_end_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_group_id (group_id),
  INDEX idx_receiver_id (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**What it does:**
- Records typing indicators
- Supports both group and direct messages
- Tracks typing start and end times

**Expected result**: `Query OK, 0 rows affected` (if table already exists)

---

### Query 13: Create Blocked Users Table (if not exists)

**Purpose**: Track blocked users

```sql
CREATE TABLE IF NOT EXISTS blocked_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  blocked_user_id INT NOT NULL,
  blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reason VARCHAR(255),
  UNIQUE KEY unique_block (user_id, blocked_user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_blocked_at (blocked_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**What it does:**
- Records user blocks
- One entry per blocked relationship
- Prevents duplicate blocks

**Expected result**: `Query OK, 0 rows affected` (if table already exists)

---

### Query 14: Convert Users Table to UTF8MB4

**Purpose**: Support emoji and special characters

```sql
ALTER TABLE users 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**What it does:**
- Enables full UTF-8 support including emoji
- Updates table collation

**Expected result**: `Query OK, X rows affected`

---

### Query 15: Convert Messages Table to UTF8MB4

**Purpose**: Support emoji in messages

```sql
ALTER TABLE messages 
CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**What it does:**
- Enables full UTF-8 support including emoji
- Updates table collation

**Expected result**: `Query OK, X rows affected`

---

### Query 16: Optimize All Tables

**Purpose**: Clean up tables and free space

```sql
OPTIMIZE TABLE users, messages, groups, contacts, group_members, conversations, notifications, sessions, message_reads, typing_indicators, blocked_users;
```

**What it does:**
- Defragments tables
- Frees up disk space
- Rebuilds indexes
- Improves performance

**Expected result**: `Table is already up to date`

---

## ✅ Verification Steps

### After Running Queries

Check if migration was successful:

```sql
-- See all tables
SHOW TABLES;

-- Check users table structure
DESCRIBE users;

-- Check if password_hash exists
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='users' AND COLUMN_NAME='password_hash';

-- Check if totp_secret_enc exists
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='users' AND COLUMN_NAME='totp_secret_enc';

-- View all indexes
SHOW INDEX FROM users;

-- Check database size
SELECT 
  TABLE_NAME, 
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS `Size (MB)` 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'varta_db' 
ORDER BY (data_length + index_length) DESC;
```

---

## 🔧 Using the Migration Script

Instead of running each query manually, execute the complete script:

```bash
# Backup first (recommended)
mysqldump -u varta_user -p varta_db > backup.sql

# Run all queries at once
mysql -u varta_user -p varta_db < /path/to/database/ALTER_TABLES.sql

# Verify with integrity checker
php check-db-integrity.php
```

---

## ❌ Rollback Procedure (If needed)

If migration causes issues:

```bash
# Restore from backup
mysql -u varta_user -p varta_db < backup.sql

# Verify restoration
mysql -u varta_user -p varta_db -e "SELECT COUNT(*) as user_count FROM users;"
```

---

## 📊 Expected Database Structure After Migration

```
Users Table Fields:
├── id (INT) PRIMARY KEY
├── username (VARCHAR)
├── email (VARCHAR)
├── password_hash (VARCHAR)
├── first_name (VARCHAR)
├── middle_name (VARCHAR) NEW
├── last_name (VARCHAR)
├── phone (VARCHAR)
├── avatar_path (VARCHAR) NEW
├── bio (VARCHAR) NEW
├── status (ENUM) NEW
├── role (ENUM) NEW
├── last_login (TIMESTAMP) NEW
├── totp_secret_enc (LONGTEXT) NEW
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP) NEW

New Tables:
├── notifications - User notifications
├── sessions - Session management
├── message_reads - Message read tracking
├── typing_indicators - Typing status
└── blocked_users - User blocks

New Indexes:
├── idx_email on users
├── idx_username on users
├── idx_status on users
└── Multiple indexes on messages, groups, contacts
```

---

## 🆘 Troubleshooting

### Error: "Unknown column 'X'"
- Column may already exist
- Query will skip and continue with `IF NOT EXISTS` clause

### Error: "Duplicate entry"
- Might happen with UNIQUE indexes
- Check if column already exists with `DESCRIBE users;`

### Slow Query Execution
- Normal for CONVERT TO CHARACTER SET on large tables
- Wait for completion, don't interrupt

### Table is already up to date
- OPTIMIZE command ran but no changes were needed
- This is expected on subsequent runs

---

## 📞 Support

For issues during migration:

1. **Check backup**: Your backup.sql file is safe
2. **Review logs**: Check PHP error logs for details
3. **Run integrity checker**: `php check-db-integrity.php`
4. **Check health endpoint**: Visit `/health-check.php`

---

**Version**: 1.0  
**Last Updated**: March 2026  
**Database**: varta_db  
**Charset**: utf8mb4_unicode_ci
