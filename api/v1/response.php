<?php
/**
 * API Response Handler
 * Standardized response format for all API endpoints
 */

header('Content-Type: application/json');

class ApiResponse {
    public static function success($data = null, $message = "Success", $statusCode = 200) {
        http_response_code($statusCode);
        return json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ]);
    }

    public static function error($message = "Error", $statusCode = 400, $errors = null) {
        http_response_code($statusCode);
        return json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('c')
        ]);
    }

    public static function paginated($items, $total, $page, $limit, $message = "Success") {
        http_response_code(200);
        return json_encode([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ],
            'timestamp' => date('c')
        ]);
    }
}

// Middleware for authorization check
function requireAuth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
    }
    return $_SESSION['user_id'];
}

// Get JSON input
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

// Sanitize input
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
