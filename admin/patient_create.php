<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $data = $_POST ?: read_json_body();

    $first = trim($data['first_name'] ?? '');
    $last = trim($data['last_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');
    $insurance = trim($data['insurance_provider'] ?? '');

    if (!$first || !$last) {
        throw new Exception('First and last name are required');
    }

    $stmt = $pdo->prepare('INSERT INTO patients (first_name, last_name, email, phone, address, insurance_provider, created_by, created_at)
                           VALUES (:f, :l, :e, :p, :addr, :ins, :uid, NOW())');
    $stmt->execute([':f'=>$first, ':l'=>$last, ':e'=>$email ?: null, ':p'=>$phone ?: null, ':addr'=>$address ?: null, ':ins'=>$insurance ?: null, ':uid'=>$_SESSION['user_id']]);

    echo json_encode(['status'=>'success','id'=>$pdo->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


