<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $new_password = trim($_POST['new_password']);
    $profile_image = $user['profile_image'];

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $profile_image = 'profile_' . time() . '_' . $user['username'] . '.' . $ext;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], __DIR__ . '/../images/' . $profile_image);
        }
    }

    // Update query
    if (!empty($new_password)) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET fullname=?, email=?, phone=?, profile_image=?, password_hash=? WHERE id=?";
        $params = [$fullname, $email, $phone, $profile_image, $password_hash, $user_id];
    } else {
        $sql = "UPDATE users SET fullname=?, email=?, phone=?, profile_image=? WHERE id=?";
        $params = [$fullname, $email, $phone, $profile_image, $user_id];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $_SESSION['fullname'] = $fullname;
    $message = "Profile updated successfully!";

    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .profile-card { max-width: 600px; margin: 50px auto; border-radius: 15px; }
        .profile-img { border-radius: 50%; width: 120px; height: 120px; object-fit: cover; }
        .btn-custom { border-radius: 25px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card profile-card shadow-sm p-4">
        <h4 class="text-center mb-4"><i class="fa fa-user-cog me-2"></i>Profile Management</h4>

        <?php if ($message): ?>
            <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="text-center mb-3">
                <img src="../images/<?= htmlspecialchars($user['profile_image']); ?>" 
                     alt="Profile" class="profile-img mb-2">
                <div>
                    <input type="file" name="profile_image" class="form-control mt-2" accept="image/*">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">New Password (optional)</label>
                <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current password">
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-custom px-4"><i class="fa fa-save me-2"></i>Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary btn-custom px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
