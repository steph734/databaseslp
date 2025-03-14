<?php
session_start();
require_once '../database/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $customer_id = trim($_POST['customer_id']);
        $status = $_POST['status'];
        $start_date = $_POST['start_date'];
        $renewal_date = $_POST['renewal_date'];
        $createdbyid = $_SESSION['admin_id']; // Replace with actual user ID logic
        $createdate = date('Y-m-d H:i:s');

        // Validate customer_id exists in the Customer table
        $check_query = "SELECT customer_id FROM Customer WHERE customer_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Invalid Customer ID: $customer_id does not exist.");
        }

        // Insert new membership
        $insert_query = "INSERT INTO membership (customer_id, status, date_repairs, date_renewal, createdbyid, createdate) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssss", $customer_id, $status, $start_date, $renewal_date, $createdbyid, $createdate);

        if ($stmt->execute()) {
            header("Location: ../resource/layout/web-layout.php?page=membership"); // Update path
            exit;
        } else {
            throw new Exception("Failed to create membership.");
        }
    }
} catch (Exception $e) {
    error_log("Error in createmember.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$conn->close();
?>