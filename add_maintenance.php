<?php
require 'db.php'; // this connects to your database

// Get POST data
$machine_id = $_POST['machine_id'] ?? null;
$machine_type = $_POST['machine_type'] ?? null;
$description = $_POST['description'] ?? null;
$performed_by = $_POST['performed_by'] ?? null;
$maintenance_date = $_POST['maintenance_date'] ?? null;

// Simple validation
if (!$machine_id || !$machine_type || !$description || !$performed_by || !$maintenance_date) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Auto generate next due date (+15 days)
$next_due_date = date('Y-m-d', strtotime($maintenance_date . ' +15 days'));

// Insert into database
try {
    $stmt = $pdo->prepare("INSERT INTO maintenance_logs (machine_id, machine_type, description, performed_by, maintenance_date, next_due_date)
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$machine_id, $machine_type, $description, $performed_by, $maintenance_date, $next_due_date]);

    echo json_encode(['status' => 'success', 'message' => 'Maintenance log added successfully!']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
