<?php
include '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = isset($_POST['customerid']) ? trim($_POST['customerid']) : '';
    $status_id = isset($_POST['status']) ? trim($_POST['status']) : '';
    $date_renewal = isset($_POST['daterenewal']) ? trim($_POST['daterenewal']) : null;
    $created_by = 1; // Change this to the logged-in user ID
    $created_date = date("Y-m-d H:i:s");

    // Validate input
    if (empty($customer_id) || empty($status_id)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit();
    }

    // Check if customer already has a membership
    $checkQuery = "SELECT * FROM membership WHERE customer_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Customer already has a membership."]);
        exit();
    }
    
    // Insert new membership
    $query = "INSERT INTO membership (customer_id, status, date_renewal, createdbyid, createdate) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $customer_id, $status_id, $date_renewal, $created_by, $created_date);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Membership added successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding membership: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
header("Location: ../resource/layout/web-layout.php?page=membership");
exit();