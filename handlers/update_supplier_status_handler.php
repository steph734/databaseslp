<?php
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $status = $_POST['status'];

    // Validate status (ensure it's one of the enum values)
    $valid_statuses = ['pending', 'active', 'inactive'];
    if (!in_array($status, $valid_statuses)) {
        echo "Invalid status";
        exit;
    }

    // Update the status in the database
    $stmt = $conn->prepare("UPDATE Supplier SET status = ? WHERE supplier_id = ?");
    $stmt->bind_param("si", $status, $supplier_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Database error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method";
}
