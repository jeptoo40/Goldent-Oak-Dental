<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: read_json_body();

    $patient_id = (int)($data['patient_id'] ?? 0);
    $total = (float)($data['total'] ?? 0);
    $paid = isset($data['paid']) ? (float)$data['paid'] : 0.0;
    $status = $data['status'] ?? 'unpaid';
    $notes = trim($data['notes'] ?? '');
    if ($patient_id <= 0) throw new Exception('patient_id required');

    // Generate unique invoice number INV-YYYYMMDD-XXXXXX
    function generateInvoiceNo(PDO $pdo): string {
        for ($i = 0; $i < 5; $i++) {
            $candidate = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $chk = $pdo->prepare('SELECT 1 FROM invoices WHERE invoice_no = :no LIMIT 1');
            $chk->execute([':no' => $candidate]);
            if (!$chk->fetch()) return $candidate;
        }
        // last resort include microtime
        return 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
    $invoice_no = generateInvoiceNo($pdo);

    $stmt = $pdo->prepare('INSERT INTO invoices (invoice_no, patient_id, total, paid, status, notes, created_at)
                           VALUES (:no, :pid, :total, :paid, :status, :notes, NOW())');
    $stmt->execute([':no'=>$invoice_no, ':pid'=>$patient_id, ':total'=>$total, ':paid'=>$paid, ':status'=>$status, ':notes'=>$notes ?: null]);

    echo json_encode(['status'=>'success','id'=>$pdo->lastInsertId(),'invoice_no'=>$invoice_no]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


