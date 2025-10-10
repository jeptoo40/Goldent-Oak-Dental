<?php
// Session check helper
// Visit after successful login: http://localhost/doctor/whoami.php

header('Content-Type: application/json');
session_start();

$out = [
    'session_status' => session_status(),
    'session_id' => session_id(),
    'user_id' => $_SESSION['user_id'] ?? null,
    'fullname' => $_SESSION['fullname'] ?? null,
    'role' => $_SESSION['role'] ?? null,
];

echo json_encode($out);


