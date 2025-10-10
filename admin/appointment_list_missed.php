<?php
require_once __DIR__ . '/_require_admin.php';

try {
    // Missed = past date with status not completed or cancelled
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 50)));
    $stmt = $pdo->prepare("SELECT a.id, a.appointment_date, a.time_slot, a.status, p.first_name, p.last_name, p.phone, p.email
                           FROM appointments a JOIN patients p ON a.patient_id = p.id
                           WHERE a.appointment_date < CURDATE() AND a.status NOT IN ('completed','cancelled')
                           ORDER BY a.appointment_date DESC, a.time_slot ASC
                           LIMIT :limit");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['status'=>'success','data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


