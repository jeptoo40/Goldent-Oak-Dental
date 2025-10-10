<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: read_json_body();

    $appointment_id = (int)($data['appointment_id'] ?? 0);
    $date = trim($data['appointment_date'] ?? '');
    $time_slot = trim($data['time_slot'] ?? '');
    $service = trim($data['service'] ?? '');
    $status = $data['status'] ?? null; // allow editing status
    $notes = trim($data['notes'] ?? '');

    if ($appointment_id <= 0 || !$date || !$time_slot) throw new Exception('Missing required fields');

    $day_of_week = date('l', strtotime($date));

    $sql = 'UPDATE appointments SET appointment_date = :d, day_of_week = :dow, time_slot = :t';
    $params = [':d'=>$date, ':dow'=>$day_of_week, ':t'=>$time_slot, ':id'=>$appointment_id];
    if ($service !== '') { $sql .= ', service = :svc'; $params[':svc'] = $service; }
    if ($status !== null) { $sql .= ', status = :status'; $params[':status'] = $status; }
    if ($notes !== '') { $sql .= ', notes = :notes'; $params[':notes'] = $notes; }
    $sql .= ' WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Notify patient via email and provide WhatsApp/SMS link
    try {
        $q = $pdo->prepare('SELECT a.appointment_date, a.time_slot, p.email, p.phone, p.first_name, p.last_name FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.id = :id');
        $q->execute([':id'=>$appointment_id]);
        $info = $q->fetch(PDO::FETCH_ASSOC);
        if ($info) {
            $full = trim(($info['first_name']??'').' '.($info['last_name']??''));
            $dateTxt = date('M d, Y', strtotime($info['appointment_date'])) . ' ' . ($info['time_slot'] ?? '');
            $message = "Hello $full, your appointment has been rescheduled to $dateTxt.";

            // Basic PHP mail (configure SMTP in php.ini for real use)
            if (!empty($info['email'])) {
                @mail($info['email'], 'Appointment Rescheduled', $message);
            }
            // Log notification intent (could be extended to a messages table)
            error_log('Reschedule notice: '. $message . ' Phone: ' . ($info['phone'] ?? ''));
        }
    } catch (Throwable $e) {
        // non-fatal
        error_log('Notify error: '.$e->getMessage());
    }

    echo json_encode(['status'=>'success']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


