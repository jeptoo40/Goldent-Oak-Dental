<?php
require_once __DIR__ . '/db.php';

// One-time admin seeder. Delete after running successfully.

$adminUserId = 'DOC001';
$adminUsername = 'admin';
$adminFullname = 'Administrator';
$adminEmail = 'admin@example.com';
$adminPhone = '0000000000';
$plainPassword = 'Admin@123'; // change after first login

// Pass ?force=1 to always reset the password hash for the admin
$force = isset($_GET['force']) && $_GET['force'] == '1';

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE user_id = :uid OR email = :email');
    $stmt->execute([':uid' => $adminUserId, ':email' => $adminEmail]);
    $exists = $stmt->fetchColumn();

    if ($exists && !$force) {
        echo "Admin already exists. Use ?force=1 to reset password.\n";
        $pdo->rollBack();
        exit;
    }

    $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

    if ($exists && $force) {
        $upd = $pdo->prepare('UPDATE users SET password_hash = :hash, role = :role WHERE id = :id');
        $upd->execute([':hash' => $passwordHash, ':role' => 'admin', ':id' => $exists]);
    } else {
        $ins = $pdo->prepare('INSERT INTO users (user_id, username, fullname, email, phone, password_hash, profile_image, role, created_at)
                               VALUES (:user_id, :username, :fullname, :email, :phone, :password_hash, :profile_image, :role, NOW())');
        $ins->execute([
            ':user_id' => $adminUserId,
            ':username' => $adminUsername,
            ':fullname' => $adminFullname,
            ':email' => $adminEmail,
            ':phone' => $adminPhone,
            ':password_hash' => $passwordHash,
            ':profile_image' => 'profile.png',
            ':role' => 'admin',
        ]);
    }

    $pdo->commit();
    echo $exists && $force ? "Admin password reset.\n" : "Default admin created.\n";
    echo "Login with: ID/email: {$adminUserId} or {$adminEmail} | Password: {$plainPassword}\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
    exit(1);
}


