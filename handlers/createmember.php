<?php
session_start();
require_once '../database/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $customer_id = $_POST['customer_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $renewal_date = $_POST['renewal_date'] ?? '';
    $createdbyid = $_SESSION['user_id'] ?? 1; // Assuming you have user_id in session

    if (empty($customer_id) || empty($status)) {
        throw new Exception('Customer ID and status are required');
    }

    $stmt = $conn->prepare("INSERT INTO membership (customer_id, status, date_repairs, date_renewal, createdbyid, createdate) 
                           VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isssi", $customer_id, $status, $start_date, $renewal_date, $createdbyid);
    
    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=membership");
        exit();
    } else {
        throw new Exception('Failed to create member');
    }

} catch (Exception $e) {
    error_log("Error in createmember.php: " . $e->getMessage());
    header("Location: ../resource/layout/web-layout.php?page=membership" . urlencode($e->getMessage()));
    exit();
} finally {
    $stmt->close();
    $conn->close();
}
?>