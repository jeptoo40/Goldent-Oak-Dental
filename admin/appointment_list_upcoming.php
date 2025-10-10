<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $days = max(1, min(30, (int)($_GET['days'] ?? 7)));
    $limit = max(1, min(50, (int)($_GET['limit'] ?? 20)));
    $stmt = $pdo->prepare("SELECT a.id, a.appointment_date, a.time_slot, a.status, p.first_name, p.last_name, p.email
                           FROM appointments a JOIN patients p ON a.patient_id = p.id
                           WHERE a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                           ORDER BY a.appointment_date ASC LIMIT :limit");
    $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['status'=>'success','data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


