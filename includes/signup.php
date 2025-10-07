<?php
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Safely read all fields using null coalescing
    $id = trim($_POST['id'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($id) || empty($username) || empty($fullname) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if ID or email already exists
        $check = $pdo->prepare("SELECT * FROM users WHERE id = :id OR email = :email");
        $check->execute([':id' => $id, ':email' => $email]);
        if ($check->fetch()) {
            $error = "User ID or Email already exists.";
        } else {
            // Hash password and insert
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (id, username, fullname, password_hash, role, email, phone, created_at)
                VALUES (:id, :username, :fullname, :password_hash, 'admin', :email, :phone, NOW())
            ");
            $stmt->execute([
                ':id' => $id,
                ':username' => $username,
                ':fullname' => $fullname,
                ':password_hash' => $password_hash,
                ':email' => $email,
                ':phone' => $phone
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);
            exit;
            
            
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Signup | Dental Clinic</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
  <div class="card shadow p-4" style="max-width: 420px; width: 100%;">
    <div class="text-center mb-3">
      <img src="../logo-removebg-preview.png" alt="Clinic Logo" width="90">
      <h4 class="mt-2">Admin Signup</h4>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">User ID</label>
        <input type="text" name="id" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Register</button>
      <div class="text-center mt-3">
        <a href="login.php">Already have an account? Login</a>
      </div>
    </form>
  </div>
</body>
</html>
