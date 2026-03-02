
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../resources/db.php';
require_once __DIR__ . '/../resources/flash.php';

session_start();
header('Content-Type: application/json');

/** @var mysqli $conn */
global $conn;

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    $userId = $_SESSION['user_id'];
    $action = $_GET['action'] ?? 'generate';

    // ✅ Initialize Google Authenticator
    require_once __DIR__ . '/../vendor/autoload.php';
    $ga = new PHPGangsta_GoogleAuthenticator();

    if ($action === 'generate') {
        // ✅ Generate new secret
        $secret = $ga->createSecret();
        
        // Store temporarily in session
        $_SESSION['temp_totp_secret'] = $secret;

        // ✅ Get user info for QR code
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            throw new Exception('User not found');
        }

        // ✅ Generate QR code URL (using Google Charts API)
        $appName = 'VartaSphere';
        // FIX: Pass array with size parameter instead of integer
        $qrCodeUrl = $ga->getQRCodeGoogleUrl(
            "{$appName} ({$user['email']})",
            $secret,
            'VartaSphere',
            ['width' => 200, 'height' => 200]  // ✅ FIXED: Use array instead of int
        );

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'message' => 'TOTP secret generated. Scan QR code with authenticator app.'
        ]);

    } elseif ($action === 'verify') {
        // ✅ Verify TOTP code before enabling
        $code = trim($_POST['code'] ?? '');
        $secret = $_SESSION['temp_totp_secret'] ?? null;

        if (!$secret) {
            throw new Exception('No TOTP secret found. Generate one first.');
        }

        if (empty($code) || strlen($code) !== 6) {
            throw new Exception('Invalid code format');
        }

        // ✅ Verify code (with 2-window tolerance)
        if (!$ga->verifyCode($secret, $code, 2)) {
            throw new Exception('Invalid TOTP code. Please try again.');
        }

        // ✅ Save to database
        $stmt = $conn->prepare("
            UPDATE users SET totp_enabled = 1, totp_secret = ? WHERE id = ?
        ");
        $stmt->bind_param("si", $secret, $userId);
        $stmt->execute();

        // ✅ Clear temporary session
        unset($_SESSION['temp_totp_secret']);

        set_flash('Two-Factor Authentication enabled successfully!', 'success');

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Two-Factor Authentication enabled'
        ]);

    } elseif ($action === 'disable') {
        // ✅ Disable TOTP
        $password = $_POST['password'] ?? '';

        if (empty($password)) {
            throw new Exception('Password required to disable 2FA');
        }

        // Verify password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Invalid password');
        }

        // Disable TOTP
        $stmt = $conn->prepare("
            UPDATE users SET totp_enabled = 0, totp_secret = NULL WHERE id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        unset($_SESSION['temp_totp_secret']);

        set_flash('Two-Factor Authentication disabled', 'info');

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Two-Factor Authentication disabled'
        ]);

    } elseif ($action === 'status') {
        // ✅ Get TOTP status
        $stmt = $conn->prepare("SELECT totp_enabled FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'totp_enabled' => (bool)$user['totp_enabled']
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
