<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: read_json_body();

    $patient_id = (int)($data['patient_id'] ?? 0);
    $visit_id = isset($data['visit_id']) ? (int)$data['visit_id'] : null;
    $description = trim($data['description'] ?? '');
    $status = $data['status'] ?? 'open';
    if ($patient_id <= 0 || !$description) throw new Exception('Missing required fields');

    $stmt = $pdo->prepare('INSERT INTO lab_cases (patient_id, visit_id, description, status, created_at)
                           VALUES (:pid, :vid, :desc, :status, NOW())');
    $stmt->execute([':pid'=>$patient_id, ':vid'=>$visit_id ?: null, ':desc'=>$description, ':status'=>$status]);

    echo json_encode(['status'=>'success','id'=>$pdo->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


