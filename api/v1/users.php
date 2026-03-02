<?php
/**
 * Users API Microservice
 * Handles profile, contacts, blocking, status updates
 */
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../resources/db.php';
require_once __DIR__ . '/response.php';

header('Content-Type: application/json');

$userId = requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'profile';
$input = $method !== 'GET' ? getJsonInput() : [];

global $conn;

switch ($action) {
    case 'profile':
        if ($method === 'GET') {
            handleGetProfile($userId, $conn);
        } else {
            handleUpdateProfile($userId, $conn, $input);
        }
        break;
    case 'contacts':
        handleGetContacts($userId, $conn);
        break;
    case 'search':
        handleSearchUsers($userId, $conn);
        break;
    case 'add-contact':
        handleAddContact($userId, $conn, $input);
        break;
    case 'remove-contact':
        handleRemoveContact($userId, $conn, $input);
        break;
    case 'block-user':
        handleBlockUser($userId, $conn, $input);
        break;
    case 'unblock-user':
        handleUnblockUser($userId, $conn, $input);
        break;
    case 'set-status':
        handleSetStatus($userId, $conn, $input);
        break;
    case 'get-user':
        handleGetUser($userId, $conn);
        break;
    default:
        exit(ApiResponse::error('Unknown action', 400));
}

function handleGetProfile($userId, $conn) {
    $stmt = $conn->prepare("
        SELECT id, username, email, first_name, last_name, phone, avatar_path, 
               bio, status, created_at FROM users WHERE id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        exit(ApiResponse::error('User not found', 404));
    }

    exit(ApiResponse::success($user));
}

function handleUpdateProfile($userId, $conn, $input) {
    $firstName = sanitize($input['first_name'] ?? '');
    $lastName = sanitize($input['last_name'] ?? '');
    $phone = sanitize($input['phone'] ?? '');
    $bio = sanitize($input['bio'] ?? '');

    $stmt = $conn->prepare("
        UPDATE users SET first_name = ?, last_name = ?, phone = ?, bio = ? WHERE id = ?
    ");
    $stmt->bind_param("ssssi", $firstName, $lastName, $phone, $bio, $userId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to update profile', 500));
    }

    exit(ApiResponse::success(null, 'Profile updated'));
}

function handleGetContacts($userId, $conn) {
    $status = $_GET['status'] ?? 'accepted';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, intval($_GET['limit'] ?? 20));
    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.avatar_path, u.bio, u.status, c.created_at
        FROM contacts c
        JOIN users u ON c.contact_id = u.id
        WHERE c.user_id = ? AND c.status = ?
        ORDER BY c.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("isii", $userId, $status, $limit, $offset);
    $stmt->execute();
    $contacts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM contacts WHERE user_id = ? AND status = ?");
    $countStmt->bind_param("is", $userId, $status);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    exit(ApiResponse::paginated($contacts, $total, $page, $limit));
}

function handleSearchUsers($userId, $conn) {
    $query = sanitize($_GET['q'] ?? '');
    $limit = min(20, intval($_GET['limit'] ?? 10));

    if (strlen($query) < 2) {
        exit(ApiResponse::error('Query too short', 400));
    }

    $searchTerm = '%' . $query . '%';
    $stmt = $conn->prepare("
        SELECT id, username, avatar_path, bio, status FROM users 
        WHERE (username LIKE ? OR first_name LIKE ? OR last_name LIKE ?)
        AND id != ? LIMIT ?
    ");
    
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $userId, $limit);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    exit(ApiResponse::success($results, 'Search results'));
}

function handleAddContact($userId, $conn, $input) {
    $contactId = intval($input['contact_id'] ?? 0);

    if (!$contactId || $contactId === $userId) {
        exit(ApiResponse::error('Invalid contact ID', 400));
    }

    $stmt = $conn->prepare("
        INSERT INTO contacts (user_id, contact_id, status) VALUES (?, ?, 'pending')
        ON DUPLICATE KEY UPDATE status = 'pending'
    ");
    
    $stmt->bind_param("ii", $userId, $contactId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to add contact', 500));
    }

    exit(ApiResponse::success(null, 'Contact request sent', 201));
}

function handleRemoveContact($userId, $conn, $input) {
    $contactId = intval($input['contact_id'] ?? 0);

    if (!$contactId) {
        exit(ApiResponse::error('Contact ID required', 400));
    }

    $stmt = $conn->prepare("DELETE FROM contacts WHERE user_id = ? AND contact_id = ?");
    $stmt->bind_param("ii", $userId, $contactId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to remove contact', 500));
    }

    exit(ApiResponse::success(null, 'Contact removed'));
}

function handleBlockUser($userId, $conn, $input) {
    $blockedId = intval($input['user_id'] ?? 0);

    if (!$blockedId || $blockedId === $userId) {
        exit(ApiResponse::error('Invalid user ID', 400));
    }

    $stmt = $conn->prepare("
        INSERT INTO blocked_users (user_id, blocked_user_id) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE created_at = NOW()
    ");
    
    $stmt->bind_param("ii", $userId, $blockedId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to block user', 500));
    }

    exit(ApiResponse::success(null, 'User blocked'));
}

function handleUnblockUser($userId, $conn, $input) {
    $blockedId = intval($input['user_id'] ?? 0);

    if (!$blockedId) {
        exit(ApiResponse::error('User ID required', 400));
    }

    $stmt = $conn->prepare("DELETE FROM blocked_users WHERE user_id = ? AND blocked_user_id = ?");
    $stmt->bind_param("ii", $userId, $blockedId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to unblock user', 500));
    }

    exit(ApiResponse::success(null, 'User unblocked'));
}

function handleSetStatus($userId, $conn, $input) {
    $status = sanitize($input['status'] ?? 'offline');
    
    if (!in_array($status, ['online', 'offline', 'away'])) {
        exit(ApiResponse::error('Invalid status', 400));
    }

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $userId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to update status', 500));
    }

    exit(ApiResponse::success(null, 'Status updated'));
}

function handleGetUser($userId, $conn) {
    $contactId = intval($_GET['user_id'] ?? 0);

    if (!$contactId) {
        exit(ApiResponse::error('User ID required', 400));
    }

    $stmt = $conn->prepare("
        SELECT id, username, avatar_path, bio, status, first_name, last_name 
        FROM users WHERE id = ?
    ");
    $stmt->bind_param("i", $contactId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        exit(ApiResponse::error('User not found', 404));
    }

    exit(ApiResponse::success($user));
}
?>
