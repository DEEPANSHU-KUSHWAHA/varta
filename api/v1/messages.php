<?php
/**
 * Messages API Microservice
 * Handles sending, retrieving, editing, deleting messages
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../resources/db.php';
require_once __DIR__ . '/response.php';

header('Content-Type: application/json');

$userId = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'fetch';
$input = $method !== 'GET' ? getJsonInput() : [];

global $conn;

switch ($action) {
    case 'fetch':
        handleFetchMessages($userId, $conn);
        break;
    case 'send':
        handleSendMessage($userId, $conn, $input);
        break;
    case 'edit':
        handleEditMessage($userId, $conn, $input);
        break;
    case 'delete':
        handleDeleteMessage($userId, $conn, $input);
        break;
    case 'get-conversation':
        handleGetConversation($userId, $conn);
        break;
    case 'mark-read':
        handleMarkAsRead($userId, $conn, $input);
        break;
    default:
        exit(ApiResponse::error('Unknown action', 400));
}

function handleFetchMessages($userId, $conn) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, intval($_GET['limit'] ?? 20));
    $offset = ($page - 1) * $limit;
    $type = $_GET['type'] ?? 'all'; // all, direct, group

    $whereClause = "(sender_id = ? OR recipient_id = ? OR group_id IN (SELECT group_id FROM group_members WHERE user_id = ?))";
    if ($type === 'direct') {
        $whereClause = "(sender_id = ? OR recipient_id = ?) AND group_id IS NULL";
    } elseif ($type === 'group') {
        $whereClause = "group_id IN (SELECT gm.group_id FROM group_members gm WHERE gm.user_id = ?)";
    }

    // Fetch messages with proper filtering
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.avatar_path,
               (SELECT COUNT(*) FROM message_reads WHERE message_id = m.id) as read_count
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE $whereClause AND m.is_deleted = FALSE
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ");

    if ($type === 'direct') {
        $stmt->bind_param("iii", $userId, $userId, $limit, $offset);
    } else {
        $stmt->bind_param("iii", $userId, $userId, $limit, $offset);
    }

    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total FROM messages m
        WHERE $whereClause AND m.is_deleted = FALSE
    ");
    if ($type === 'direct') {
        $countStmt->bind_param("iii", $userId, $userId, $userId);
    } else {
        $countStmt->bind_param("iii", $userId, $userId, $userId);
    }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    exit(ApiResponse::paginated($messages, $total, $page, $limit));
}

function handleSendMessage($userId, $conn, $input) {
    $content = sanitize($input['content'] ?? '');
    $recipientId = intval($input['recipient_id'] ?? 0);
    $groupId = intval($input['group_id'] ?? 0);
    $messageType = $input['message_type'] ?? 'text';
    $mediaUrl = $input['media_url'] ?? null;

    if (empty($content) && !$mediaUrl) {
        exit(ApiResponse::error('Content or media is required', 400));
    }

    if (!$recipientId && !$groupId) {
        exit(ApiResponse::error('Recipient or group is required', 400));
    }

    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, recipient_id, group_id, content, message_type, media_url)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param("iiisss", $userId, $recipientId > 0 ? $recipientId : null, 
                      $groupId > 0 ? $groupId : null, $content, $messageType, $mediaUrl);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to send message', 500));
    }

    exit(ApiResponse::success([
        'message_id' => $stmt->insert_id,
        'created_at' => date('c')
    ], 'Message sent', 201));
}

function handleEditMessage($userId, $conn, $input) {
    $messageId = intval($input['message_id'] ?? 0);
    $newContent = sanitize($input['content'] ?? '');

    if (!$messageId || empty($newContent)) {
        exit(ApiResponse::error('Message ID and content required', 400));
    }

    // Verify ownership
    $verify = $conn->prepare("SELECT sender_id FROM messages WHERE id = ?");
    $verify->bind_param("i", $messageId);
    $verify->execute();
    $msg = $verify->get_result()->fetch_assoc();

    if (!$msg || $msg['sender_id'] != $userId) {
        exit(ApiResponse::error('Unauthorized', 403));
    }

    $stmt = $conn->prepare("UPDATE messages SET content = ?, is_edited = TRUE, edited_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $newContent, $messageId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to edit message', 500));
    }

    exit(ApiResponse::success(null, 'Message updated'));
}

function handleDeleteMessage($userId, $conn, $input) {
    $messageId = intval($input['message_id'] ?? 0);

    if (!$messageId) {
        exit(ApiResponse::error('Message ID required', 400));
    }

    // Verify ownership
    $verify = $conn->prepare("SELECT sender_id FROM messages WHERE id = ?");
    $verify->bind_param("i", $messageId);
    $verify->execute();
    $msg = $verify->get_result()->fetch_assoc();

    if (!$msg || $msg['sender_id'] != $userId) {
        exit(ApiResponse::error('Unauthorized', 403));
    }

    $stmt = $conn->prepare("UPDATE messages SET is_deleted = TRUE WHERE id = ?");
    $stmt->bind_param("i", $messageId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to delete message', 500));
    }

    exit(ApiResponse::success(null, 'Message deleted'));
}

function handleGetConversation($userId, $conn) {
    $contactId = intval($_GET['contact_id'] ?? 0);
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, intval($_GET['limit'] ?? 30));
    $offset = ($page - 1) * $limit;

    if (!$contactId) {
        exit(ApiResponse::error('Contact ID required', 400));
    }

    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.avatar_path
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?)
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("iiiiii", $userId, $contactId, $contactId, $userId, $limit, $offset);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total FROM messages
        WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?)
    ");
    $countStmt->bind_param("iiii", $userId, $contactId, $contactId, $userId);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    exit(ApiResponse::paginated(array_reverse($messages), $total, $page, $limit));
}

function handleMarkAsRead($userId, $conn, $input) {
    $messageId = intval($input['message_id'] ?? 0);

    if (!$messageId) {
        exit(ApiResponse::error('Message ID required', 400));
    }

    $stmt = $conn->prepare("
        INSERT INTO message_reads (message_id, user_id) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE read_at = NOW()
    ");
    
    $stmt->bind_param("ii", $messageId, $userId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to mark as read', 500));
    }

    exit(ApiResponse::success(null, 'Message marked as read'));
}
?>
