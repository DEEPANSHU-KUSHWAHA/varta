<?php
/**
 * Groups API Microservice
 * Handles group creation, management, members
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
        handleListGroups($userId, $conn);
        break;
    case 'create':
        handleCreateGroup($userId, $conn, $input);
        break;
    case 'get':
        handleGetGroup($userId, $conn);
        break;
    case 'update':
        handleUpdateGroup($userId, $conn, $input);
        break;
    case 'delete':
        handleDeleteGroup($userId, $conn, $input);
        break;
    case 'add-member':
        handleAddMember($userId, $conn, $input);
        break;
    case 'remove-member':
        handleRemoveMember($userId, $conn, $input);
        break;
    case 'get-members':
        handleGetMembers($userId, $conn);
        break;
    default:
        exit(ApiResponse::error('Unknown action', 400));
}

function handleListGroups($userId, $conn) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, intval($_GET['limit'] ?? 20));
    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare("
        SELECT g.*, COUNT(gm.id) as member_count
        FROM groups g
        JOIN group_members gm ON g.id = gm.group_id
        WHERE gm.user_id = ?
        GROUP BY g.id
        ORDER BY g.updated_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("iii", $userId, $limit, $offset);
    $stmt->execute();
    $groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count
    $countStmt = $conn->prepare("
        SELECT COUNT(DISTINCT g.id) as total FROM groups g
        JOIN group_members gm ON g.id = gm.group_id
        WHERE gm.user_id = ?
    ");
    $countStmt->bind_param("i", $userId);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    exit(ApiResponse::paginated($groups, $total, $page, $limit));
}

function handleCreateGroup($userId, $conn, $input) {
    $name = sanitize($input['name'] ?? '');
    $description = sanitize($input['description'] ?? '');
    $isPrivate = intval($input['is_private'] ?? 0);

    if (empty($name)) {
        exit(ApiResponse::error('Group name required', 400));
    }

    // Create group
    $stmt = $conn->prepare("
        INSERT INTO groups (name, description, creator_id, is_private)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("ssii", $name, $description, $userId, $isPrivate);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to create group', 500));
    }

    $groupId = $stmt->insert_id;

    // Add creator as admin member
    $memberStmt = $conn->prepare("
        INSERT INTO group_members (group_id, user_id, role)
        VALUES (?, ?, 'admin')
    ");
    $memberStmt->bind_param("ii", $groupId, $userId);
    $memberStmt->execute();

    exit(ApiResponse::success([
        'group_id' => $groupId,
        'name' => $name
    ], 'Group created', 201));
}

function handleGetGroup($userId, $conn) {
    $groupId = intval($_GET['group_id'] ?? 0);

    if (!$groupId) {
        exit(ApiResponse::error('Group ID required', 400));
    }

    // Check if user is member
    $checkStmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $groupId, $userId);
    $checkStmt->execute();

    if ($checkStmt->get_result()->num_rows === 0) {
        exit(ApiResponse::error('Not a member of this group', 403));
    }

    $stmt = $conn->prepare("
        SELECT g.*, u.username as creator_name, COUNT(gm.id) as member_count
        FROM groups g
        LEFT JOIN users u ON g.creator_id = u.id
        LEFT JOIN group_members gm ON g.id = gm.group_id
        WHERE g.id = ?
        GROUP BY g.id
    ");
    
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $group = $stmt->get_result()->fetch_assoc();

    if (!$group) {
        exit(ApiResponse::error('Group not found', 404));
    }

    exit(ApiResponse::success($group));
}

function handleUpdateGroup($userId, $conn, $input) {
    $groupId = intval($input['group_id'] ?? 0);
    $name = sanitize($input['name'] ?? '');
    $description = sanitize($input['description'] ?? '');

    if (!$groupId) {
        exit(ApiResponse::error('Group ID required', 400));
    }

    // Check if user is admin
    $checkStmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $groupId, $userId);
    $checkStmt->execute();
    $member = $checkStmt->get_result()->fetch_assoc();

    if (!$member || $member['role'] !== 'admin') {
        exit(ApiResponse::error('Only admins can update group', 403));
    }

    $stmt = $conn->prepare("UPDATE groups SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $groupId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to update group', 500));
    }

    exit(ApiResponse::success(null, 'Group updated'));
}

function handleDeleteGroup($userId, $conn, $input) {
    $groupId = intval($input['group_id'] ?? 0);

    if (!$groupId) {
        exit(ApiResponse::error('Group ID required', 400));
    }

    // Check if user is creator
    $checkStmt = $conn->prepare("SELECT creator_id FROM groups WHERE id = ?");
    $checkStmt->bind_param("i", $groupId);
    $checkStmt->execute();
    $group = $checkStmt->get_result()->fetch_assoc();

    if (!$group || $group['creator_id'] != $userId) {
        exit(ApiResponse::error('Only creator can delete group', 403));
    }

    $stmt = $conn->prepare("DELETE FROM groups WHERE id = ?");
    $stmt->bind_param("i", $groupId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to delete group', 500));
    }

    exit(ApiResponse::success(null, 'Group deleted'));
}

function handleAddMember($userId, $conn, $input) {
    $groupId = intval($input['group_id'] ?? 0);
    $memberId = intval($input['member_id'] ?? 0);

    if (!$groupId || !$memberId) {
        exit(ApiResponse::error('Group and member IDs required', 400));
    }

    // Check if user is admin
    $checkStmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $groupId, $userId);
    $checkStmt->execute();
    $member = $checkStmt->get_result()->fetch_assoc();

    if (!$member || $member['role'] === 'member') {
        exit(ApiResponse::error('Only admins/mods can add members', 403));
    }

    $stmt = $conn->prepare("
        INSERT INTO group_members (group_id, user_id) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE joined_at = NOW()
    ");
    
    $stmt->bind_param("ii", $groupId, $memberId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to add member', 500));
    }

    exit(ApiResponse::success(null, 'Member added', 201));
}

function handleRemoveMember($userId, $conn, $input) {
    $groupId = intval($input['group_id'] ?? 0);
    $memberId = intval($input['member_id'] ?? 0);

    if (!$groupId || !$memberId) {
        exit(ApiResponse::error('Group and member IDs required', 400));
    }

    // Check if user is admin or the member being removed
    $checkStmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $groupId, $userId);
    $checkStmt->execute();
    $member = $checkStmt->get_result()->fetch_assoc();

    if (!$member || ($member['role'] === 'member' && $userId != $memberId)) {
        exit(ApiResponse::error('Unauthorized', 403));
    }

    $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $groupId, $memberId);

    if (!$stmt->execute()) {
        exit(ApiResponse::error('Failed to remove member', 500));
    }

    exit(ApiResponse::success(null, 'Member removed'));
}

function handleGetMembers($userId, $conn) {
    $groupId = intval($_GET['group_id'] ?? 0);
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, intval($_GET['limit'] ?? 20));
    $offset = ($page - 1) * $limit;

    if (!$groupId) {
        exit(ApiResponse::error('Group ID required', 400));
    }

    // Check membership
    $checkStmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $groupId, $userId);
    $checkStmt->execute();

    if ($checkStmt->get_result()->num_rows === 0) {
        exit(ApiResponse::error('Not a member', 403));
    }

    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.avatar_path, u.status, gm.role, gm.joined_at
        FROM group_members gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.group_id = ?
        ORDER BY gm.role DESC, u.username ASC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->bind_param("iii", $groupId, $limit, $offset);
    $stmt->execute();
    $members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get total count
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM group_members WHERE group_id = ?");
    $countStmt->bind_param("i", $groupId);
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_assoc()['total'];

    exit(ApiResponse::paginated($members, $total, $page, $limit));
}
?>
