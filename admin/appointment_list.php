<?php
require_once __DIR__ . '/_require_admin.php';

try {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;
    $status = $_GET['status'] ?? '';
    $from = $_GET['from'] ?? '';
    $to = $_GET['to'] ?? '';
    $q = trim($_GET['q'] ?? '');

    $conds = [];
    $params = [];
    if ($status !== '') { $conds[] = 'a.status = :status'; $params[':status'] = $status; }
    if ($from !== '') { $conds[] = 'a.appointment_date >= :from'; $params[':from'] = $from; }
    if ($to !== '') { $conds[] = 'a.appointment_date <= :to'; $params[':to'] = $to; }
    if ($q !== '') { $conds[] = '(p.first_name LIKE :q OR p.last_name LIKE :q OR p.email LIKE :q OR p.phone LIKE :q)'; $params[':q'] = "%$q%"; }
    $where = $conds ? ('WHERE ' . implode(' AND ', $conds)) : '';

    $sql = "SELECT SQL_CALC_FOUND_ROWS a.id, a.patient_id, a.service, a.appointment_date, a.day_of_week, a.time_slot, a.status, a.notes, a.source, a.reminder_sent, p.first_name, p.last_name, p.email, p.phone
            FROM appointments a JOIN patients p ON a.patient_id = p.id
            $where
            ORDER BY a.appointment_date DESC, a.time_slot ASC
            LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k=>$v) { $stmt->bindValue($k, $v); }
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


