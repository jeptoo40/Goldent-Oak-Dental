<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $user_id = trim($_POST['user_id'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$user_id || !$username || !$fullname || !$email || !$phone || !$password) {
        throw new Exception('Please fill in all fields.');
    }

    // Check if user already exists
    $check = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id OR email = :email");
    $check->execute([':user_id' => $user_id, ':email' => $email]);
    if ($check->fetch()) {
        throw new Exception('User ID or Email already exists.');
    }

    // Handle profile image
    $profile_image = 'profile.png'; // default
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $profile_image = 'profile_'.time().'_'.$username.'.'.$ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], __DIR__.'/../images/'.$profile_image);
        }
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into DB
    $stmt = $pdo->prepare("
        INSERT INTO users (user_id, username, fullname, email, phone, password_hash, profile_image, role, created_at)
        VALUES (:user_id, :username, :fullname, :email, :phone, :password_hash, :profile_image, 'admin', NOW())
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':username' => $username,
        ':fullname' => $fullname,
        ':email' => $email,
        ':phone' => $phone,
        ':password_hash' => $password_hash,
        ':profile_image' => $profile_image
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
