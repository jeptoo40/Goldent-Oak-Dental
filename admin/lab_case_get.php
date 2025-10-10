<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) throw new Exception('id required');
    $stmt = $pdo->prepare('SELECT id, patient_id, description, status FROM lab_cases WHERE id = :id');
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception('not found');
    echo json_encode(['status'=>'success','data'=>$row]);
} catch (Throwable $e) {
    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


