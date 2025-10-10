<?php
// Admin guard and shared bootstrap for admin endpoints
header('Content-Type: application/json');

session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden: admin access required']);
    exit;
}

// Helper: read JSON body if Content-Type is application/json
function read_json_body(): array {
    if (!isset($_SERVER['CONTENT_TYPE'])) return [];
    if (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false) return [];
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}


