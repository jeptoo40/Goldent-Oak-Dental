<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Admin.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT fullname, email, profile_image, password_hash FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$fullname = $user['fullname'] ?? '';
$email = $user['email'] ?? '';
$profile_image = $user['profile_image'] ?? 'profile.png';
$current_hash = $user['password_hash'] ?? '';

// Handle AJAX POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    $newFullname = trim($_POST['fullname'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate required fields
    if (empty($newFullname) || empty($newEmail)) {
        $response['message'] = 'Full name and email cannot be empty.';
        echo json_encode($response);
        exit;
    }

    // Validate password change if fields are filled
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (!password_verify($currentPassword, $current_hash)) {
            $response['message'] = 'Current password is incorrect.';
            echo json_encode($response);
            exit;
        }
        if ($newPassword !== $confirmPassword) {
            $response['message'] = 'New password and confirmation do not match.';
            echo json_encode($response);
            exit;
        }
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    } else {
        $passwordHash = $current_hash; // keep existing password
    }

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newFileName = 'profile_' . $user_id . '.' . $ext;
            $uploadPath = __DIR__ . '/../images/' . $newFileName;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                $profile_image = $newFileName;
            }
        }
    }

    // Update database
    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, profile_image = ?, password_hash = ? WHERE id = ?");
    if ($stmt->execute([$newFullname, $newEmail, $profile_image, $passwordHash, $user_id])) {
        $_SESSION['fullname'] = $newFullname;
        $_SESSION['profile_image'] = $profile_image;
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
        $response['fullname'] = $newFullname;
        $response['profile_image'] = $profile_image;
    } else {
        $response['message'] = 'Failed to update profile.';
    }

    echo json_encode($response);
    exit;
}
?>

<!-- HTML Form -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Profile Management</h5>
    </div>
    <div class="card-body">
        <form id="profileForm" enctype="multipart/form-data">
            <div class="mb-3 text-center">
                <img id="profilePreview" src="../images/<?php echo htmlspecialchars($profile_image); ?>" 
                     alt="Profile" class="rounded-circle mb-2" width="100" height="100">
                <input type="file" name="profile_image" class="form-control form-control-sm" onchange="previewImage(this)">
            </div>

            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" name="fullname" id="fullname" class="form-control" value="<?php echo htmlspecialchars($fullname); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <hr>
            <h6>Change Password</h6>
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" name="current_password" id="current_password" class="form-control">
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
            </div>
            <small class="text-muted">Leave password fields empty if you don't want to change it.</small>

            <button type="submit" class="btn btn-primary mt-2">Save Changes</button>
        </form>
    </div>
</div>

<script>
// Preview selected image
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('profilePreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

// AJAX form submission
document.getElementById('profileForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const fd = new FormData(this);

    try {
        const res = await fetch('profile.php', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();

        if (data.success) {
            alert(data.message);

            // Update right sidebar instantly
            const sidebarName = document.getElementById('sidebarFullname');
            const sidebarImg = document.getElementById('sidebarProfileImage');
            if (sidebarName) sidebarName.textContent = data.fullname;
            if (sidebarImg) sidebarImg.src = '../images/' + data.profile_image + '?t=' + Date.now();
        } else {
            alert(data.message);
        }
    } catch (err) {
        console.error(err);
        alert('Error updating profile.');
    }
});
</script>
