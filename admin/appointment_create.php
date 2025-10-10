<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: read_json_body();

    $patient_id = (int)($data['patient_id'] ?? 0);
    $service = trim($data['service'] ?? 'General Consultation');
    $date = trim($data['appointment_date'] ?? '');
    $time_slot = trim($data['time_slot'] ?? '');
    $status = $data['status'] ?? 'pending';
    $notes = trim($data['notes'] ?? '');
    $source = $data['source'] ?? 'website';

    if ($patient_id <= 0 || !$date || !$time_slot) throw new Exception('Missing required fields');
    $day_of_week = date('l', strtotime($date));

    $stmt = $pdo->prepare('INSERT INTO appointments (patient_id, service, appointment_date, day_of_week, time_slot, status, notes, source, reminder_sent, created_at, created_by)
                           VALUES (:pid, :svc, :d, :dow, :t, :status, :notes, :src, 0, NOW(), :uid)');
    $stmt->execute([':pid'=>$patient_id, ':svc'=>$service, ':d'=>$date, ':dow'=>$day_of_week, ':t'=>$time_slot, ':status'=>$status, ':notes'=>$notes ?: null, ':src'=>$source, ':uid'=>$_SESSION['user_id']]);

    echo json_encode(['status'=>'success','id'=>$pdo->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


