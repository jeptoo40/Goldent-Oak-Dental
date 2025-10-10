<?php
require_once __DIR__ . '/_require_admin.php';

// Create invoice for an appointment with optional line items
// POST JSON: { appointment_id, items: [{description, quantity, unit_price}], notes? }
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = read_json_body();
    if (!$data) $data = $_POST; // fallback form

    $appointment_id = (int)($data['appointment_id'] ?? 0);
    $items = $data['items'] ?? [];
    $notes = trim($data['notes'] ?? '');
    if ($appointment_id <= 0) throw new Exception('appointment_id required');

    $ap = $pdo->prepare('SELECT a.id, a.patient_id FROM appointments a WHERE a.id = :id');
    $ap->execute([':id'=>$appointment_id]);
    $appt = $ap->fetch(PDO::FETCH_ASSOC);
    if (!$appt) throw new Exception('Appointment not found');

    $pdo->beginTransaction();

    $amount = 0.0;
    foreach ($items as $it) {
        $qty = (int)($it['quantity'] ?? 1);
        $price = (float)($it['unit_price'] ?? 0);
        $amount += $qty * $price;
    }

    $ins = $pdo->prepare('INSERT INTO invoices (patient_id, invoice_date, amount, status, notes, created_at)
                          VALUES (:pid, CURRENT_DATE, :amount, "unpaid", :notes, NOW())');
    $ins->execute([':pid'=>$appt['patient_id'], ':amount'=>$amount, ':notes'=>$notes ?: null]);
    $invoice_id = (int)$pdo->lastInsertId();

    if (!empty($items)) {
        $itemStmt = $pdo->prepare('INSERT INTO invoice_items (invoice_id, description, quantity, unit_price) VALUES (:iid, :d, :q, :p)');
        foreach ($items as $it) {
            $itemStmt->execute([
                ':iid'=>$invoice_id,
                ':d'=>trim($it['description'] ?? 'Service'),
                ':q'=>(int)($it['quantity'] ?? 1),
                ':p'=>(float)($it['unit_price'] ?? 0)
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(['status'=>'success','invoice_id'=>$invoice_id,'amount'=>$amount]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


