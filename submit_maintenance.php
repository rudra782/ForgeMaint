<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forgemaint";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$machine_id = $_POST['machine_id'];
$machine_type = $_POST['machine_type'];
$description = $_POST['description'];
$performed_by = $_POST['performed_by'];
$maintenance_date = $_POST['maintenance_date'];

// Calculate next due date (15 days after maintenance date)
$next_due_date = date('Y-m-d', strtotime($maintenance_date . ' +15 days'));

// Insert data into maintenance_logs table
$sql = "INSERT INTO maintenance_logs (machine_id, machine_type, description, performed_by, maintenance_date, next_due_date)
VALUES ('$machine_id', '$machine_type', '$description', '$performed_by', '$maintenance_date', '$next_due_date')";

if ($conn->query($sql) === TRUE) {
    echo "New maintenance log added successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
