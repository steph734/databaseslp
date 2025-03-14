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
    // Retrieve form data with correct field names
    $supplier_return_id = $_POST['supplier_return_id'];
    $supplier_id = $_POST['supplier_id'];
    $return_reason = $_POST['return_reason'];
    $return_date = $_POST['return_date'];
    $refund_status = $_POST['refund_status'];
    $updatedbyid = $_SESSION['admin_id'];

    try {
        // Enable MySQLi Exception Mode
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Validate required fields from form
        if (!$supplier_return_id || !$supplier_id || !$return_reason || !$return_date || !$refund_status) {
            $_SESSION['error'] = "All required fields must be filled.";
            header("Location: ../resource/layout/web-layout.php?page=returns&error=missing_fields");
            exit();
        }

        // Set updatedate: use form value or current timestamp
        $updatedate = !empty($_POST['updatedate']) ? $_POST['updatedate'] : date('Y-m-d H:i:s');

        // Debugging: Log values before execution
        error_log("Updating supplier return: ID=$supplier_return_id, Supplier=$supplier_id, Reason=$return_reason");

        // Update query without product_id (not in form)
        $query = "UPDATE supplierreturn SET 
                    supplier_id = ?, 
                    return_reason = ?, 
                    return_date = ?, 
                    refund_status = ?, 
                    updatedbyid = ?, 
                    updatedate = ? 
                  WHERE supplier_return_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssisi", $supplier_id, $return_reason, $return_date, $refund_status, $updatedbyid, $updatedate, $supplier_return_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Supplier return updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update supplier return.";
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        // Log error for debugging
        error_log("Error updating supplier return: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred. Please try again.";
    }

    // Single redirect after all processing
    header("Location: ../resource/layout/web-layout.php?page=returns");
    exit();
} else {
    // Handle non-POST requests
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../resource/layout/web-layout.php?page=returns");
    exit();
}
?>