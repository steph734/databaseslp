<?php
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiving_id = $_POST['receiving_id'];
    $status = $_POST['status'];

    // Validate status (ensure it's one of the enum values)
    $valid_statuses = ['pending', 'received', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        echo "Invalid status";
        exit;
    }

    // Update the status in the database
    $stmt = $conn->prepare("UPDATE receiving SET status = ? WHERE receiving_id = ?");
    $stmt->bind_param("si", $status, $receiving_id);

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
