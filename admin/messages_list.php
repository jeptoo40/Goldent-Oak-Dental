<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $limit = max(1, min(200, (int)($_GET['limit'] ?? 50)));
    $stmt = $pdo->prepare('SELECT id, full_name, email, subject, message, created_at FROM messages ORDER BY created_at DESC LIMIT :limit');
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['status'=>'success','data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


