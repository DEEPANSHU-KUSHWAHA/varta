<?php

/**
 * Users API Microservice - FIXED
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
    case 'get-user':
        handleGetUser($userId, $conn);
        break;
    default:
        exit(ApiResponse::error('Unknown action', 400));
}

function handleGetProfile($userId, $conn) {
    $stmt = $conn->prepare("
        SELECT id, username, email, first_name, last_name, phone, bio, avatar_path, status, created_at 
        FROM users WHERE id = ?
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
        SELECT c.contact_id, u.username, u.first_name, u.last_name, u.avatar_path, u.status
        FROM contacts c
        JOIN users u ON c.contact_id = u.id
        WHERE c.user_id = ? AND c.status = ?
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("isii", $userId, $status, $limit, $offset);
    $stmt->execute();
    $contacts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
        exit(ApiResponse::error('Search term too short', 400));
    }

    $searchTerm = '%' . $query . '%';
    $stmt = $conn->prepare("
        SELECT id, username, first_name, last_name, avatar_path, status
        FROM users 
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
        INSERT INTO contacts (user_id, contact_id, status, created_at)
        VALUES (?, ?, 'pending', NOW())
        ON DUPLICATE KEY UPDATE status = 'pending'
    ");
    
    $stmt->bind_param("ii", $userId, $contactId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to add contact', 500));
    }

    exit(ApiResponse::success(null, 'Contact request sent', 201));
}

function handleGetUser($userId, $conn) {
    $contactId = intval($_GET['user_id'] ?? 0);

    if (!$contactId) {
        exit(ApiResponse::error('User ID required', 400));
    }

    $stmt = $conn->prepare("
        SELECT id, username, first_name, last_name, avatar_path, status, bio
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