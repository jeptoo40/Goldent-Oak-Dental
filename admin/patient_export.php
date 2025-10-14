<?php
require_once __DIR__ . '/../includes/fpdf185/fpdf.php';
require_once __DIR__ . '/../includes/db.php';

// Create new PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Patient List - Golden Oak Dental Clinic', 0, 1, 'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(10, 10, 'ID', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'First Name', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Last Name', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Phone', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Insurance', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Address', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Registered', 1, 1, 'C', true);

// Fetch data
$stmt = $pdo->query("SELECT id, first_name, last_name, email, phone, insurance_provider, address, created_at FROM patients ORDER BY id ASC");
$pdf->SetFont('Arial', '', 9);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pdf->Cell(10, 8, $row['id'], 1);
    $pdf->Cell(30, 8, $row['first_name'], 1);
    $pdf->Cell(30, 8, $row['last_name'], 1);
    $pdf->Cell(50, 8, $row['email'], 1);
    $pdf->Cell(30, 8, $row['phone'], 1);
    $pdf->Cell(40, 8, $row['insurance_provider'], 1);
    $pdf->Cell(50, 8, $row['address'], 1);
    $pdf->Cell(35, 8, date('d/m/Y H:i', strtotime($row['created_at'])), 1, 1);
}

// Output the PDF for download
$pdf->Output('D', 'patients_list.pdf');
exit;
?>
