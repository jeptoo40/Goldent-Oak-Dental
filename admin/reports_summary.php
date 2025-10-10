<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $summary = [];

    $summary['patients_total'] = (int)$pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
    $summary['appointments_today'] = (int)$pdo->query('SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()')->fetchColumn();
    $summary['invoices_unpaid'] = (int)$pdo->query("SELECT COUNT(*) FROM invoices WHERE status IN ('unpaid','partial')")->fetchColumn();
    $summary['lab_open'] = (int)$pdo->query("SELECT COUNT(*) FROM lab_cases WHERE status IN ('open','in_progress')")->fetchColumn();

    echo json_encode(['status'=>'success','data'=>$summary]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


