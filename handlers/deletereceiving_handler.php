<?php
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $receiving_id = intval($_POST['id']); // Sanitize input

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM receiving WHERE receiving_id = ?");
    $stmt->bind_param("i", $receiving_id);
    
    if ($stmt->execute()) {
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            echo "success";
        } else {
            echo "No record found with ID: " . $receiving_id;
        }
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    
    $stmt->close();
} else {
    echo "Invalid request";
}

$conn->close();
?>