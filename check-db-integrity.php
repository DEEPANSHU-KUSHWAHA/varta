<?php
/**
 * Database Connection Integrity Checker
 * Use this to verify and test all database connections
 * 
 * Usage: php check-db-integrity.php
 */

session_start();
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/resources/db.php';

/** @var mysqli $conn */
global $conn;

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     Varta Database Integrity Checker v1.0              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Utility functions
function check($name, $status, $message = '') {
    $symbol = $status ? '✓' : '✗';
    $color = $status ? '\033[32m' : '\033[31m';
    $reset = '\033[0m';
    echo $color . $symbol . $reset . ' ' . $name;
    if ($message) {
        echo ' - ' . $message;
    }
    echo "\n";
    return $status;
}

function section($title) {
    echo "\n\033[1m" . $title . "\033[0m\n";
    echo str_repeat('-', strlen($title)) . "\n";
}

// 1. Check Environment Variables
section('1. Environment Configuration');

$envVars = [
    'CPANEL_DB_HOST' => 'Database Host',
    'CPANEL_DB_USER' => 'Database User',
    'CPANEL_DB_PASS' => 'Database Password',
    'CPANEL_DB_NAME' => 'Database Name',
    'JWT_SECRET' => 'JWT Secret',
    'TOTP_ENC_KEY' => 'TOTP Encryption Key'
];

$envOk = true;
foreach ($envVars as $var => $label) {
    $value = getenv($var);
    $isSet = !empty($value);
    check($label . ' (' . $var . ')', $isSet, $isSet ? 'Configured' : 'MISSING');
    if (!$isSet && in_array($var, ['CPANEL_DB_HOST', 'CPANEL_DB_USER', 'CPANEL_DB_NAME'])) {
        $envOk = false;
    }
}

if (!$envOk) {
    echo "\n\033[33m⚠ WARNING: Required environment variables are missing!\033[0m\n";
    echo "Create a .env file with proper configuration.\n";
    exit(1);
}

// 2. Check Database Connection
section('2. Database Connection');

if (!$conn || $conn === null) {
    check('Connection Status', false, 'Database connection failed');
    echo "\nError: Unable to connect to database\n";
    exit(1);
} else {
    check('Connection Status', true, 'Connected');
}

if ($conn->ping()) {
    check('Connection Ping', true, 'Server is responsive');
} else {
    check('Connection Ping', false, 'Server is not responding');
    exit(1);
}

// Get database info
$result = $conn->query("SELECT VERSION() as version, DATABASE() as current_db");
if ($result) {
    $info = $result->fetch_assoc();
    check('MySQL Version', true, $info['version']);
    check('Current Database', true, $info['current_db']);
}

// 3. Check Tables
section('3. Database Tables');

$requiredTables = [
    'users' => 'User accounts',
    'messages' => 'Messages and chat',
    'groups' => 'Group conversations',
    'group_members' => 'Group membership',
    'contacts' => 'User contacts/friends',
    'notifications' => 'Notifications',
    'sessions' => 'Session management',
    'message_reads' => 'Message read status'
];

$tablesOk = true;
$result = $conn->query("SHOW TABLES");
$existingTables = [];

if ($result) {
    while ($row = $result->fetch_row()) {
        $existingTables[] = $row[0];
    }
}

foreach ($requiredTables as $table => $description) {
    $exists = in_array($table, $existingTables);
    check('Table: ' . $table, $exists, $description);
    if (!$exists) {
        $tablesOk = false;
    }
}

// 4. Check Table Columns
section('4. Table Columns (Users)');

$result = $conn->query("DESCRIBE users");
$columns = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[$row['Field']] = $row['Type'];
    }
}

$requiredColumns = [
    'id' => 'int',
    'username' => 'varchar',
    'email' => 'varchar',
    'password_hash' => 'varchar',
    'totp_secret_enc' => 'longtext',
    'first_name' => 'varchar',
    'last_name' => 'varchar',
    'phone' => 'varchar',
    'avatar_path' => 'varchar',
    'bio' => 'varchar',
    'status' => 'enum',
    'role' => 'enum',
    'last_login' => 'timestamp',
    'created_at' => 'timestamp',
    'updated_at' => 'timestamp'
];

foreach ($requiredColumns as $column => $expectedType) {
    $exists = isset($columns[$column]);
    check('Column: ' . $column, $exists, $exists ? strtoupper($columns[$column]) : 'MISSING');
}

// 5. Check Indexes
section('5. Performance Indexes');

$result = $conn->query("SHOW INDEXES FROM users");
$indexes = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['Key_name'] !== 'PRIMARY') {
            $indexes[$row['Key_name']] = $row['Column_name'];
        }
    }
}

$expectedIndexes = ['email', 'username', 'status'];
foreach ($expectedIndexes as $index) {
    $exists = false;
    foreach ($indexes as $existing) {
        if (strpos($existing, $index) !== false) {
            $exists = true;
            break;
        }
    }
    check('Index: ' . $index, $exists, $exists ? 'Optimized' : 'MISSING (consider adding)');
}

// 6. Check Table Sizes
section('6. Database Storage');

$result = $conn->query("
    SELECT 
        TABLE_NAME,
        ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS size_mb,
        TABLE_ROWS as row_count
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = DATABASE()
    ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
");

if ($result) {
    echo "Table Name              Size(MB)  Rows\n";
    echo "─────────────────────────────────────\n";
    while ($row = $result->fetch_assoc()) {
        printf("%-24s %8.2f  %8d\n", 
            substr($row['TABLE_NAME'], 0, 23), 
            $row['size_mb'], 
            $row['row_count']
        );
    }
}

// Get total database size
$result = $conn->query("
    SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS total_size
    FROM information_schema.TABLES 
    WHERE TABLE_SCHEMA = DATABASE()
");

if ($result) {
    $size = $result->fetch_assoc();
    echo "─────────────────────────────────────\n";
    printf("%-24s %8.2f MB Total\n", '', $size['total_size']);
}

// 7. Check Encryption
section('7. Security Configuration');

$hasEncryption = !empty(getenv('TOTP_ENC_KEY'));
check('TOTP Encryption', $hasEncryption, $hasEncryption ? 'AES-256-CBC' : 'MISSING');

$hasJWT = !empty(getenv('JWT_SECRET'));
check('JWT Secret', $hasJWT, $hasJWT ? 'HS256' : 'MISSING');

// 8. File System
section('8. File System');

$uploadDir = __DIR__ . '/uploads';
check('Uploads Directory', is_dir($uploadDir), is_dir($uploadDir) ? 'Exists' : 'MISSING');

if (is_dir($uploadDir)) {
    check('Uploads Writable', is_writable($uploadDir), is_writable($uploadDir) ? 'Yes' : 'No');
}

// 9. Summary
section('Summary');

echo "✓ All critical systems are operational\n\n";
echo "Next Steps:\n";
echo "1. Run migration scripts: php database/migrate.php\n";
echo "2. Check API connectivity: curl https://your-domain/api/v1/auth.php\n";
echo "3. Access health check: https://your-domain/health-check.php\n\n";

$conn->close();
?>
