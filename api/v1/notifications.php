<?php
/**
 * Notifications API Microservice
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../resources/db.php';
require_once __DIR__ . '/response.php';

header('Content-Type: application/json');

$userId = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$input = $method !== 'GET' ? getJsonInput() : [];

global $conn;

switch ($action) {
    case 'list':
        handleListNotifications($userId, $conn);
        break;
    case 'mark-read':
        handleMarkAsRead($userId, $conn, $input);
        break;
    case 'delete':
        handleDeleteNotification($userId, $conn, $input);
        break;
    case 'unread-count':
        handleGetUnreadCount($userId, $conn);
        break;
    default:
        exit(ApiResponse::error('Unknown action', 400));
}

function handleListNotifications($userId, $conn) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, intval($_GET['limit'] ?? 20));
    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare("
        SELECT * FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("iii", $userId, $limit, $offset);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?");
    $countStmt->bind_param("i", $userId);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    exit(ApiResponse::paginated($notifications, $total, $page, $limit));
}

function handleMarkAsRead($userId, $conn, $input) {
    $notificationId = intval($input['notification_id'] ?? 0);

    if (!$notificationId) {
        exit(ApiResponse::error('Notification ID required', 400));
    }

    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notificationId, $userId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to update notification', 500));
    }

    exit(ApiResponse::success(null, 'Notification marked as read'));
}

function handleDeleteNotification($userId, $conn, $input) {
    $notificationId = intval($input['notification_id'] ?? 0);

    if (!$notificationId) {
        exit(ApiResponse::error('Notification ID required', 400));
    }

    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notificationId, $userId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to delete notification', 500));
    }

    exit(ApiResponse::success(null, 'Notification deleted'));
}

function handleGetUnreadCount($userId, $conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    exit(ApiResponse::success(['unread_count' => $result['unread']]));
}
?>
