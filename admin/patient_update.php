<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) throw new Exception('id required');

    $fields = ['first_name','last_name','gender','phone','email','id_number','address','insurance_provider','payment_mode'];
    $sets = [];
    $params = [':id'=>$id, ':updated_by'=>$_SESSION['user_id']];
    foreach ($fields as $f) {
        if (array_key_exists($f, $data)) { $sets[] = "$f = :$f"; $params[":$f"] = trim((string)$data[$f]); }
    }
    $sets[] = 'updated_by = :updated_by';
    $sets[] = 'updated_at = NOW()';
    $sql = 'UPDATE patients SET ' . implode(', ', $sets) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status'=>'success']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


