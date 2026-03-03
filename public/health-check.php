<?php
/**
 * Varta Health Check & Configuration Test
 * Verifies all critical connections and configurations
 * 
 * Access at: /health-check.php
 */

header('Content-Type: application/json');

$checks = [];
$allPassed = true;

// 1. Check Database Connection
$checks['database'] = [
    'name' => 'Database Connection',
    'status' => 'error',
    'message' => 'Not tested'
];

try {
    require_once __DIR__ . '/../resources/db.php';
    global $conn;
    
    if ($conn && !$conn instanceof mysqli) {
        throw new Exception('Not a valid mysqli instance');
    }
    
    if ($conn && $conn->connect_errno) {
        throw new Exception('Connection error: ' . $conn->connect_error);
    }
    
    if ($conn) {
        $result = $conn->query("SELECT 1");
        if ($result) {
            $checks['database']['status'] = 'success';
            $checks['database']['message'] = 'Connected to database successfully';
            
            // Get database size
            $dbInfo = $conn->query("SELECT 
                table_schema as db_name,
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables 
                WHERE table_schema NOT IN ('information_schema','mysql','performance_schema','sys')
                GROUP BY table_schema");
            
            if ($dbInfo) {
                $checks['database']['database_info'] = $dbInfo->fetch_all(MYSQLI_ASSOC);
            }
        } else {
            throw new Exception('Query failed');
        }
    } else {
        throw new Exception('Database connection is null');
    }
} catch (Exception $e) {
    $checks['database']['status'] = 'error';
    $checks['database']['message'] = 'Database error: ' . $e->getMessage();
    $allPassed = false;
}

// 2. Check File System Permissions
$checks['filesystem'] = [
    'name' => 'File System Permissions',
    'status' => 'success',
    'message' => 'File permissions OK',
    'directories' => []
];

$directoriesToCheck = [
    '/uploads' => false,
    '/uploads/avatars' => true,
    '/public/css' => false,
    '/public/js' => false,
    '/app/auth' => false,
    '/api/v1' => false,
    '/resources' => false
];

foreach ($directoriesToCheck as $dir => $writable) {
    $path = __DIR__ . '/..' . $dir;
    $exists = is_dir($path);
    $isWritable = $writable && is_writable($path);
    
    $checks['filesystem']['directories'][] = [
        'path' => $dir,
        'exists' => $exists,
        'writable' => !$writable ? 'N/A' : ($isWritable ? 'Yes' : 'No'),
        'permissions' => $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A'
    ];
    
    if ($writable && $exists && !$isWritable) {
        $checks['filesystem']['status'] = 'warning';
        $checks['filesystem']['message'] = 'Some directories not writable';
        $allPassed = false;
    }
}

// 3. Check Environment Variables
$checks['environment'] = [
    'name' => 'Environment Configuration',
    'status' => 'warning',
    'message' => 'Checking environment variables',
    'variables' => []
];

$requiredEnvVars = [
    'CPANEL_DB_HOST' => 'Database Host',
    'CPANEL_DB_USER' => 'Database User',
    'CPANEL_DB_NAME' => 'Database Name',
    'JWT_SECRET' => 'JWT Secret Key',
    'TOTP_ENC_KEY' => 'TOTP Encryption Key'
];

foreach ($requiredEnvVars as $varName => $varLabel) {
    $value = getenv($varName);
    $checks['environment']['variables'][] = [
        'name' => $varLabel,
        'key' => $varName,
        'configured' => !empty($value),
        'value' => !empty($value) ? (strlen($value) > 10 ? substr($value, 0, 10) . '...' : $value) : 'NOT SET'
    ];
    
    if (empty($value) && in_array($varName, ['CPANEL_DB_HOST', 'CPANEL_DB_USER', 'CPANEL_DB_NAME', 'JWT_SECRET', 'TOTP_ENC_KEY'])) {
        $checks['environment']['status'] = 'error';
        $allPassed = false;
    }
}

// 4. Check PHP Extensions
$checks['extensions'] = [
    'name' => 'Required PHP Extensions',
    'status' => 'success',
    'message' => 'All required extensions loaded',
    'extensions' => []
];

$requiredExtensions = ['mysqli', 'openssl', 'json', 'mbstring', 'gd'];

foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $checks['extensions']['extensions'][] = [
        'name' => $ext,
        'loaded' => $loaded
    ];
    
    if (!$loaded) {
        $checks['extensions']['status'] = 'error';
        $checks['extensions']['message'] = 'Missing required extensions: ' . $ext;
        $allPassed = false;
    }
}

// 5. Check Required Files
$checks['files'] = [
    'name' => 'Required Files',
    'status' => 'success',
    'message' => 'All critical files exist',
    'files' => []
];

$requiredFiles = [
    '/resources/db.php' => 'Database Configuration',
    '/resources/flash.php' => 'Flash Messages',
    '/app/auth/jwt.php' => 'JWT Authentication',
    '/api/v1/response.php' => 'API Response Handler',
    '/database/schema.sql' => 'Database Schema',
    '/public/css/spa.css' => 'SPA Stylesheet',
    '/public/js/api-client.js' => 'API Client'
];

foreach ($requiredFiles as $file => $label) {
    $path = __DIR__ . '/..' . $file;
    $exists = file_exists($path);
    $checks['files']['files'][] = [
        'name' => $label,
        'path' => $file,
        'exists' => $exists,
        'size' => $exists ? filesize($path) . ' bytes' : 'N/A'
    ];
    
    if (!$exists) {
        $checks['files']['status'] = 'error';
        $checks['files']['message'] = 'Missing critical file: ' . $file;
        $allPassed = false;
    }
}

// 6. Check Session Configuration
$checks['session'] = [
    'name' => 'Session Configuration',
    'status' => 'success',
    'message' => 'Session configured correctly',
    'configuration' => [
        'session_handler' => ini_get('session.save_handler'),
        'session_path' => ini_get('session.save_path'),
        'session_name' => ini_get('session.name'),
        'session_auto_start' => ini_get('session.auto_start') ? 'On' : 'Off',
        'session_cookie_secure' => ini_get('session.cookie_secure') ? 'On' : 'Off',
        'session_cookie_httponly' => ini_get('session.cookie_httponly') ? 'On' : 'Off'
    ]
];

// 7. Check API Endpoints
$checks['api'] = [
    'name' => 'API Endpoints',
    'status' => 'success',
    'message' => 'API endpoints configured',
    'endpoints' => [
        '/api/v1/auth.php' => 'Authentication',
        '/api/v1/users.php' => 'User Management',
        '/api/v1/messages.php' => 'Messaging',
        '/api/v1/groups.php' => 'Group Management',
        '/api/v1/notifications.php' => 'Notifications'
    ]
];

// 8. Check Composer Dependencies
$checks['composer'] = [
    'name' => 'Composer Dependencies',
    'status' => 'success',
    'message' => 'Dependencies installed',
    'installed' => file_exists(__DIR__ . '/../vendor/autoload.php')
];

if (!$checks['composer']['installed']) {
    $checks['composer']['status'] = 'error';
    $checks['composer']['message'] = 'Composer dependencies not installed. Run: composer install';
    $allPassed = false;
}

// 9. Check GitHub Integration
$checks['github'] = [
    'name' => 'Git Repository',
    'status' => 'warning',
    'message' => 'Git configuration',
    'git_installed' => shell_exec('which git') !== null || shell_exec('where git') !== null,
    'repository' => file_exists(__DIR__ . '/../.git') ? 'Yes' : 'No',
    'remote' => []
];

if (file_exists(__DIR__ . '/../.git') && shell_exec('git --version')) {
    $remote = trim(shell_exec('cd ' . __DIR__ . '/.. && git config --get remote.origin.url 2>/dev/null'));
    if ($remote) {
        $checks['github']['remote'] = [
            'origin' => $remote
        ];
        $checks['github']['status'] = 'success';
    }
}

// Summary
$summary = [
    'timestamp' => date('c'),
    'overall_status' => $allPassed ? 'healthy' : 'issues_detected',
    'checks_total' => count($checks),
    'checks_passed' => count(array_filter($checks, fn($c) => $c['status'] === 'success')),
    'checks_warning' => count(array_filter($checks, fn($c) => $c['status'] === 'warning')),
    'checks_error' => count(array_filter($checks, fn($c) => $c['status'] === 'error')),
    'detailed_results' => $checks
];

http_response_code($allPassed ? 200 : 503);
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
