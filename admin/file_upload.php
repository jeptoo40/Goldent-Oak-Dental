<?php
require_once __DIR__ . '/_require_admin.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Invalid request method');
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $visit_id = (int)($_POST['visit_id'] ?? 0);
    if ($patient_id <= 0) throw new Exception('patient_id required');
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) throw new Exception('file required');

    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $orig = $_FILES['file']['name'];
    $ext = pathinfo($orig, PATHINFO_EXTENSION);
    $safe = 'file_' . time() . '_' . bin2hex(random_bytes(4)) . ($ext ? ('.' . $ext) : '');
    $path = $dir . '/' . $safe;
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) throw new Exception('Upload failed');

    // Detect MIME type and size server-side
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    $mime = $finfo ? finfo_file($finfo, $path) : ($_FILES['file']['type'] ?? 'application/octet-stream');
    if ($finfo) finfo_close($finfo);
    $size = filesize($path);

    $stmt = $pdo->prepare('INSERT INTO uploads (patient_id, visit_id, filename, original_name, file_type, file_size, uploaded_by, uploaded_at)
                           VALUES (:pid, :vid, :fn, :on, :ft, :fs, :uid, NOW())');
    $stmt->execute([
        ':pid'=>$patient_id,
        ':vid'=>$visit_id ?: null,
        ':fn'=>$safe,
        ':on'=>$orig,
        ':ft'=>$mime,
        ':fs'=>$size,
        ':uid'=>$_SESSION['user_id']
    ]);

    echo json_encode(['status'=>'success','id'=>$pdo->lastInsertId(), 'filename'=>$safe]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


