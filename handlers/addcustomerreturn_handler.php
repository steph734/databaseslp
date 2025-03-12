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
    $customer_id = $_POST['customer_id'] ?? null;
    $return_reason = $_POST['return_reason'] ?? null;
    $return_date = $_POST['return_date'] ?? null;
    $refund_status = $_POST['refund_status'] ?? null;
    $total_amount = $_POST['total_amount'] ?? null;
    $createdbyid = $_SESSION['admin_id'];
    $createdate = date('Y-m-d H:i:s'); // Automatically set the creation timestamp

    // Validate required fields
    if (!$customer_id || !$return_reason || !$return_date || !$refund_status || !$total_amount) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../returns.php?error=missing_fields");
        exit();
    }

    // Call a stored procedure (if you have one) or use a prepared statement
    $stmt = $conn->prepare("INSERT INTO customerreturn (customer_id, return_reason, return_date, refund_status, total_amount, createdbyid, createdate) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdis", $customer_id, $return_reason, $return_date, $refund_status, $total_amount, $createdbyid, $createdate);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Customer return added successfully!";
        header("Location: ../resource/layout/web-layout.php?page=returns");
    } else {
        $_SESSION['error'] = "Failed to add return: " . $stmt->error;
        header("Location: ../resource/layout/web-layout.php?page=customer_returns&error=database_error");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=customer_returns&error=invalid_request");
}

$conn->close();
