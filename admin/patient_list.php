<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $q = trim($_GET['q'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $params = [];
    $where = '';
    if ($q !== '') {
        $where = 'WHERE (first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR phone LIKE :q)';
        $params[':q'] = "%$q%";
    }

    $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS id, first_name, last_name, email, phone, insurance_provider, created_at
                            FROM patients $where ORDER BY created_at DESC LIMIT :offset, :limit");
    foreach ($params as $k=>$v) { $stmt->bindValue($k, $v, PDO::PARAM_STR); }
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = (int)$pdo->query('SELECT FOUND_ROWS()')->fetchColumn();

    echo json_encode(['status'=>'success','data'=>$rows,'total'=>$total,'page'=>$page,'limit'=>$limit]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}


