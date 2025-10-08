<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $user_id_email = trim($_POST['user_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$user_id_email || !$password) {
        throw new Exception('Please fill in all fields.');
    }

   
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :input OR email = :input");
    $stmt->execute([':input' => $user_id_email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found.');
    }

    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception('Incorrect password.');
    }

    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['fullname'] = $user['fullname'];

    $_SESSION['role'] = $user['role'];

    echo json_encode(['status' => 'success', 'message' => 'Login successful!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
