<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

try {
    $first = trim($_POST['first_name'] ?? '');
$last  = trim($_POST['last_name'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$id_number = trim($_POST['id_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$insurance_provider = trim($_POST['insurance_provider'] ?? '');
$payment_mode = trim($_POST['payment_method'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$service = trim($_POST['service'] ?? '');
$date = trim($_POST['appointment_date'] ?? '');
$time_slot = trim($_POST['time_slot'] ?? '');
$notes = trim($_POST['notes'] ?? '');


    if (!$first || !$phone || !$date || !$time_slot) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        exit;
    }
    if (!empty($email)) {
        // Basic email syntax check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
            exit;
        }
    
        // Extract domain
        $domain = strtolower(substr(strrchr($email, "@"), 1));
    
        // Block common typos and fake domains
        $fake_domains = ['gmail.con', 'gmal.com', 'gmial.com', '1gmail.com', 'gmaill.com'];
        if (in_array($domain, $fake_domains)) {
            echo json_encode(['success' => false, 'message' => 'Invalid or mistyped email domain.']);
            exit;
        }
    
        // DNS check (works only on live servers)
        if (function_exists('checkdnsrr') && !checkdnsrr($domain, "MX")) {
            echo json_encode(['success' => false, 'message' => 'Invalid email domain. Please use a valid one.']);
            exit;
        }
    }
    
    $pdo->beginTransaction();

   
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = :email OR phone = :phone LIMIT 1");
    $stmt->execute([':email' => $email, ':phone' => $phone]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        $patient_id = $patient['id'];
    } else {
      
        $ref = 'PAT-' . date('Ymd') . '-' . substr(md5(uniqid()), 0, 6);
        $insert = $pdo->prepare("
        INSERT INTO patients 
        (ref_no, first_name, last_name, gender, id_number, address, phone, email, insurance_provider, payment_mode, created_by, updated_by, created_at) 
        VALUES 
        (:ref, :first, :last, :gender, :id_number, :address, :phone, :email, :insurance_provider, :payment_mode, :created_by, :updated_by, NOW())
    ");
    
    $insert->execute([
        ':ref' => $ref,
        ':first' => $first,
        ':last' => $last,
        ':gender' => $gender,
        ':id_number' => $id_number,
        ':address' => $address,
        ':phone' => $phone,
        ':email' => $email,
        ':insurance_provider' => $insurance_provider,
        ':payment_mode' => $payment_mode,
        ':created_by' => $created_by,
        ':updated_by' => $updated_by
    ]);
    
        $patient_id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("INSERT INTO appointments 
        (patient_id, service, appointment_date, day_of_week, time_slot, notes) 
        VALUES (:pid, :service, :date, :day, :time, :notes)");
    $dayOfWeek = date('l', strtotime($date));
    $stmt->execute([
        ':pid' => $patient_id,
        ':service' => $service,
        ':date' => $date,
        ':day' => $dayOfWeek,
        ':time' => $time_slot,
        ':notes' => $notes
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Appointment booked successfully!']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
