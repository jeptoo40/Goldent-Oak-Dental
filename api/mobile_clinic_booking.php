<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

try {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $id_no = trim($_POST['id_number'] ?? '');
    $payment_mode = trim($_POST['payment_mode'] ?? '');
    $insurance_provider = trim($_POST['insurance_provider'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $parking = trim($_POST['parking'] ?? '');
    $message = trim($_POST['message'] ?? '');



if (
    empty($name) || empty($email) || empty($phone) || empty($id_no) ||
    empty($payment_mode) || empty($location) || empty($parking)
) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

$domain = substr(strrchr($email, "@"), 1);
if (!checkdnsrr($domain, "MX")) {
    echo json_encode(['success' => false, 'message' => 'Invalid email domain. Please use a real email address.']);
    exit;
}


    $check = $pdo->prepare("SELECT id FROM mobile_clinic_bookings WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'This email is already registered for a booking. Please use another one.'
        ]);
        exit;
    }

    
    $stmt = $pdo->prepare("INSERT INTO mobile_clinic_bookings 
        (full_name, email, phone, id_number, payment_mode, insurance_provider, location, parking, message)
        VALUES (:name, :email, :phone, :id_no, :payment_mode, :insurance_provider, :location, :parking, :message)");

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':id_no' => $id_no,
        ':payment_mode' => $payment_mode,
        ':insurance_provider' => $insurance_provider,
        ':location' => $location,
        ':parking' => $parking,
        ':message' => $message
    ]);

    echo json_encode(['success' => true, 'message' => 'Your booking has been submitted successfully!']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
