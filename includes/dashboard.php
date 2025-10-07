<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <nav class="navbar navbar-light bg-white shadow-sm p-3 mb-4">
    <div class="container d-flex justify-content-between">
      <img src="logo.png" alt="Clinic Logo" height="40">
      <span>Welcome, <?= htmlspecialchars($_SESSION['fullname']) ?></span>
      <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
  </nav>

  <div class="container">
    <h4>Dashboard</h4>
    <p>Manage clinic operations, appointments, and staff here.</p>
  </div>
</body>
</html>
