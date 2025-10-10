<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: read_json_body();
    $id = (int)($data['appointment_id'] ?? 0);
    if ($id <= 0) throw new Exception('appointment_id required');

    $stmt = $pdo->prepare('DELETE FROM appointments WHERE id = :id');
    $stmt->execute([':id'=>$id]);
    echo json_encode(['status'=>'success']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


