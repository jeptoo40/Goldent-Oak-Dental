<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) throw new Exception('id required');

    $stmt = $pdo->prepare('SELECT filename FROM uploads WHERE id = :id');
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $file = __DIR__ . '/../uploads/' . $row['filename'];
        if (is_file($file)) @unlink($file);
    }
    $del = $pdo->prepare('DELETE FROM uploads WHERE id = :id');
    $del->execute([':id'=>$id]);
    echo json_encode(['status'=>'success']);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


