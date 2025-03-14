<?php
session_start();
require_once '../database/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $membership_id = $_POST['membership_id'] ?? '';
    $customer_id = $_POST['customer_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $renewal_date = $_POST['renewal_date'] ?? '';
    $updatedbyid = $_SESSION['user_id'] ?? 1;

    if (empty($membership_id) || empty($customer_id) || empty($status)) {
        throw new Exception('Membership ID, Customer ID, and status are required');
    }

    $stmt = $conn->prepare("UPDATE membership SET customer_id = ?, status = ?, date_repairs = ?, 
                           date_renewal = ?, updatedbyid = ?, updatedate = NOW() 
                           WHERE membership_id = ?");
    $stmt->bind_param("isssii", $customer_id, $status, $start_date, $renewal_date, $updatedbyid, $membership_id);
    
    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=membership");
        exit();
    } else {
        throw new Exception('Failed to update member');
    }

} catch (Exception $e) {
    error_log("Error in editmember.php: " . $e->getMessage());
    header("Location: ../../path/to/your/main_page.php?error=" . urlencode($e->getMessage()));
    exit();
} finally {
    $stmt->close();
    $conn->close();
}
?>