<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) throw new Exception('id required');
    $stmt = $pdo->prepare('SELECT filename, original_name, file_type FROM uploads WHERE id = :id');
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception('not found');
    $path = __DIR__ . '/../uploads/' . $row['filename'];
    if (!is_file($path)) throw new Exception('file missing');

    $mime = $row['file_type'] ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($row['original_name'] ?: $row['filename']) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
} catch (Throwable $e) {
    http_response_code(404);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}


