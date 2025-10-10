<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) throw new Exception('id required');
    $fields = ['patient_id','invoice_date','total','paid','status','notes'];
    $map = ['total'=>'amount'];
    $sets = [];
    $params = [':id'=>$id];
    foreach ($fields as $f) {
        if (array_key_exists($f, $data)) {
            $col = $map[$f] ?? $f;
            $sets[] = "$col = :$f";
            $params[":$f"] = $data[$f];
        }
    }
    if (!$sets) throw new Exception('nothing to update');
    $sql = 'UPDATE invoices SET ' . implode(', ', $sets) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status'=>'success']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


