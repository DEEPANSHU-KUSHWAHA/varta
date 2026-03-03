-- ============================================================
-- VARTA DATABASE - ALTER TABLE QUERIES
-- Use these queries to update your database schema
-- ============================================================
-- 
-- Make sure to backup your database before running these queries!
-- Run in the order specified below
--
-- ============================================================

-- 1. Ensure users table has all required columns
-- Run this to add missing fields if they don't exist

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS middle_name VARCHAR(100) AFTER first_name,
ADD COLUMN IF NOT EXISTS bio VARCHAR(255),
ADD COLUMN IF NOT EXISTS status ENUM('online','offline','away') DEFAULT 'offline' AFTER bio,
ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL AFTER status,
ADD COLUMN IF NOT EXISTS role ENUM('user','moderator','admin') DEFAULT 'user' AFTER last_login,
ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) AFTER role,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER avatar_path;

-- 2. Add indexes for better performance
ALTER TABLE users 
ADD INDEX IF NOT EXISTS idx_email (email),
ADD INDEX IF NOT EXISTS idx_username (username),
ADD INDEX IF NOT EXISTS idx_status (status),
ADD INDEX IF NOT EXISTS idx_created_at (created_at);

-- 3. Ensure password_hash column exists (in case using old 'password' field)
-- First add the new column if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255);

-- 4. If you have old 'password' column, copy data and drop it
-- UNCOMMENT ONLY IF YOU HAVE BOTH password AND password_hash COLUMNS:
-- UPDATE users SET password_hash = password WHERE password_hash IS NULL;
-- ALTER TABLE users DROP COLUMN password;

-- 5. Ensure totp_secret_enc column exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS totp_secret_enc LONGTEXT AFTER password_hash;

-- 6. Create notifications table if it doesn't exist
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255),
    type ENUM('info','success','warning','error','message','group') DEFAULT 'info',
    related_user_id INT,
    related_group_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (related_group_id) REFERENCES groups(id) ON DELETE SET NULL,
    INDEX idx_unread (user_id, is_read),
    INDEX idx_created_at (created_at)
);

-- 7. Create sessions table if it doesn't exist
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(512),
    refresh_token VARCHAR(512),
    refresh_expiry TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- 8. Create message_reads table if it doesn't exist
CREATE TABLE IF NOT EXISTS message_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_read (message_id, user_id),
    INDEX idx_user_id (user_id)
);

-- 9. Add indexes to messages table for better performance
ALTER TABLE messages 
ADD INDEX IF NOT EXISTS idx_sender_id (sender_id),
ADD INDEX IF NOT EXISTS idx_recipient_id (recipient_id),
ADD INDEX IF NOT EXISTS idx_group_id (group_id),
ADD INDEX IF NOT EXISTS idx_created_at (created_at);

-- 10. Add indexes to groups table
ALTER TABLE groups 
ADD INDEX IF NOT EXISTS idx_creator_id (creator_id),
ADD INDEX IF NOT EXISTS idx_created_at (created_at);

-- 11. Add indexes to contacts table
ALTER TABLE contacts 
ADD INDEX IF NOT EXISTS idx_user_id (user_id),
ADD INDEX IF NOT EXISTS idx_contact_id (contact_id),
ADD INDEX IF NOT EXISTS idx_status (status);

-- 12. Add indexes to group_members table
ALTER TABLE group_members 
ADD INDEX IF NOT EXISTS idx_group_id (group_id),
ADD INDEX IF NOT EXISTS idx_user_id (user_id),
ADD INDEX IF NOT EXISTS idx_role (role);

-- 13. Create typing_indicators table if it doesn't exist
CREATE TABLE IF NOT EXISTS typing_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_id INT,
    group_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- 14. Create blocked_users table if it doesn't exist
CREATE TABLE IF NOT EXISTS blocked_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    blocked_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block (user_id, blocked_user_id),
    INDEX idx_user_id (user_id)
);

-- 15. Ensure text encoding is UTF8MB4 for emoji support
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE groups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE contacts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE notifications CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 16. Optimize existing tables
OPTIMIZE TABLE users;
OPTIMIZE TABLE messages;
OPTIMIZE TABLE groups;
OPTIMIZE TABLE contacts;
OPTIMIZE TABLE group_members;
OPTIMIZE TABLE notifications;
OPTIMIZE TABLE sessions;

-- ============================================================
-- VERIFICATION QUERIES
-- ============================================================
-- Run these to verify your schema is correct:

-- Check users table structure:
-- DESC users;

-- Check if all tables exist:
-- SHOW TABLES;

-- Check table sizes:
-- SELECT 
--     TABLE_NAME,
--     ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) MB
-- FROM INFORMATION_SCHEMA.TABLES 
-- WHERE TABLE_SCHEMA = 'varta_db'
-- ORDER BY MB DESC;

-- ============================================================
-- BACKUP BEFORE RUNNING - EXECUTE AT YOUR OWN RISK!
-- ============================================================
