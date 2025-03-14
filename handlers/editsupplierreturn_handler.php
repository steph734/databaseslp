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
    $supplier_return_id = $_POST['return_id'] ?? null;
    $supplier_id = $_POST['supplier_id'] ?? null;
    $product_id = $_POST['product_id'] ?? null;
    $return_reason = $_POST['reason'] ?? null;
    $return_date = $_POST['date'] ?? null;
    $refund_status = $_POST['status'] ?? null;
    $updatedbyid = $_SESSION['admin_id']; // Track the admin who updated

    try {
        // Enable MySQLi Exception Mode
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Validate required fields
        if (!$supplier_return_id || !$supplier_id || !$product_id || !$return_reason || !$return_date || !$refund_status) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: ../resource/layout/web-layout.php?page=returns&error=missing_fields");
            exit();
        }

        // Check if updatedate is provided or use current timestamp
        $updatedate = !empty($_POST['updatedate']) ? $_POST['updatedate'] : date('Y-m-d H:i:s');

        // Debugging: Check values before execution
        error_log("Updating supplier return: ID=$supplier_return_id, Supplier=$supplier_id, Product=$product_id");

        $query = "UPDATE supplierreturn SET 
                    supplier_id = ?, 
                    product_id = ?, 
                    return_reason = ?, 
                    return_date = ?, 
                    refund_status = ?, 
                    updatedbyid = ?, 
                    updatedate = ? 
                  WHERE supplier_return_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iisssisi", $supplier_id, $product_id, $return_reason, $return_date, $refund_status, $updatedbyid, $updatedate, $supplier_return_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Supplier return updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update supplier return.";
        }

        $stmt->close();
        $conn->close();

        // Redirect only after execution
        header("Location: ../resource/layout/web-layout.php?page=returns");
        exit();
    } catch (Exception $e) {
        // Log error for debugging
        error_log("Error updating supplier return: " . $e->getMessage());

        // Show error message
        $_SESSION['error'] = "An error occurred. Please try again.";
        header("Location: ../resource/layout/web-layout.php?page=returns&error=database_error");
        exit();
    }
}
?>
