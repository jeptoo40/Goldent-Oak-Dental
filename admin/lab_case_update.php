<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) throw new Exception('id required');
    $patient_id = (int)($data['patient_id'] ?? 0);
    $description = trim($data['description'] ?? '');
    $status = trim($data['status'] ?? 'open');

    $stmt = $pdo->prepare('UPDATE lab_cases SET patient_id = :pid, description = :d, status = :s, updated_at = NOW() WHERE id = :id');
    $stmt->execute([':pid'=>$patient_id ?: null, ':d'=>$description ?: null, ':s'=>$status, ':id'=>$id]);
    echo json_encode(['status'=>'success']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


