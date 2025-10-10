<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $id = (int)($_GET['appointment_id'] ?? 0);
    if ($id <= 0) throw new Exception('appointment_id required');

    $stmt = $pdo->prepare('SELECT a.id, a.appointment_date, a.time_slot, p.first_name, p.last_name, p.phone, p.email
                           FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.id = :id');
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception('Appointment not found');

    $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    $phone = preg_replace('/\D+/', '', (string)($row['phone'] ?? ''));
    $email = $row['email'] ?? '';
    $dateTxt = date('M d, Y', strtotime($row['appointment_date'])) . ' ' . ($row['time_slot'] ?? '');
    $msg = rawurlencode("Hello $fullName, this is a reminder for your appointment on $dateTxt. Please confirm.");

    $links = [
        'whatsapp' => $phone ? ("https://wa.me/" . $phone . "?text=" . $msg) : null,
        'tel' => $phone ? ("tel:" . $phone) : null,
        'sms' => $phone ? ("sms:" . $phone . "?body=" . $msg) : null,
        'mailto' => $email ? ("mailto:" . rawurlencode($email) . "?subject=" . rawurlencode('Appointment Reminder') . "&body=" . $msg) : null
    ];

    echo json_encode(['status'=>'success','data'=>$links]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


