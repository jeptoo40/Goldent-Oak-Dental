<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $patient_id = (int)($_GET['patient_id'] ?? 0);
    $visit_id = (int)($_GET['visit_id'] ?? 0);
    $conds = [];
    $params = [];
    if ($patient_id > 0) { $conds[] = 'u.patient_id = :pid'; $params[':pid'] = $patient_id; }
    if ($visit_id > 0) { $conds[] = 'u.visit_id = :vid'; $params[':vid'] = $visit_id; }
    $where = $conds ? ('WHERE ' . implode(' AND ', $conds)) : '';
    $stmt = $pdo->prepare("SELECT u.id, u.filename, u.original_name, u.file_type, u.file_size, u.uploaded_at, p.first_name, p.last_name
                           FROM uploads u LEFT JOIN patients p ON u.patient_id=p.id $where ORDER BY u.uploaded_at DESC LIMIT 100");
    $stmt->execute($params);
    echo json_encode(['status'=>'success','data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


