<?php
include '../database/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../returns.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = $_POST['supplier_id'] ?? null;
    $return_reason = $_POST['return_reason'] ?? null;
    $return_date = $_POST['return_date'] ?? null;
    $refund_status = $_POST['refund_status'] ?? null;
    $createdbyid = $_SESSION['admin_id'];
    $createdate = date('Y-m-d H:i:s'); // Automatically set timestamp

    // Validate required fields
    if (!$supplier_id || !$return_reason || !$return_date || !$refund_status || !$createdbyid || !$createdate) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../returns.php?error=missing_fields");
        exit();
    }

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO supplierreturn (supplier_id, return_reason, return_date, refund_status, createdbyid, createdate) 
                        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $supplier_id, $return_reason, $return_date, $refund_status, $createdbyid, $createdate);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Supplier return added successfully!";
        header("Location: ../resource/layout/web-layout.php?page=returns");
    } else {
        $_SESSION['error'] = "Failed to add return: " . $stmt->error;
        header("Location: ../resource/layout/web-layout.php?page=supplier_returns&error=database_error");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=supplier_returns&error=invalid_request");
}

$conn->close();
?>