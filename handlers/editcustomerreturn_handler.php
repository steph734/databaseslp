<?php
session_start();
include '../database/database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/layout/web-layout.php?page=returns&error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_return_id = $_POST['customer_return_id']; // Ensure this field exists
    $customer_id = $_POST['customer_id'];
    $return_reason = $_POST['return_reason'];
    $refund_status = $_POST['refund_status'];
    $total_amount = $_POST['total_amount'];
    $updatedbyid = $_SESSION['admin_id']; // Track the admin who updated

    try {
        // Enable MySQLi Exception Mode
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Check if updatedate is provided or use current timestamp
        if (!empty($_POST['updatedate'])) {
            $updatedate = $_POST['updatedate'];
        } else {
            $updatedate = date('Y-m-d H:i:s'); // Current timestamp
        }

        $query = "UPDATE customerreturn SET 
                    customer_id = ?, 
                    return_reason = ?, 
                    refund_status = ?, 
                    total_amount = ?, 
                    updatedbyid = ?, 
                    updatedate = ? 
                  WHERE customer_return_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issdisi", $customer_id, $return_reason, $refund_status, $total_amount, $updatedbyid, $updatedate, $customer_return_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Customer return updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update customer return.";
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: ../resource/layout/web-layout.php?page=returns");
    exit();
}
?>
