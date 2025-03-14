<?php
session_start();
require_once '../database/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $membership_id = $_POST['membership_id'];
        $sales_id = $_POST['sales_id'];
        $total_purchase = $_POST['total_purchase'];
        $points_amount = $_POST['points_amount'];

        // Basic validation
        if (empty($membership_id) || empty($sales_id) || empty($total_purchase) || empty($points_amount)) {
            throw new Exception("All fields are required.");
        }

        // Prepare and execute insert query
        $query = "INSERT INTO points (membership_id, sales_id, total_purchase, points_amount) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdd", $membership_id, $sales_id, $total_purchase, $points_amount);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Points added successfully.";
        } else {
            throw new Exception("Failed to add points.");
        }

        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error in addpoints.php: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
}

$conn->close();
header("Location: ../resource/layout/web-layout.php?page=points"); // Adjust this to your actual page
exit();
?>