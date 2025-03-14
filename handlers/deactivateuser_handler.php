<?php
session_start();
include '../database/database.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "You must be logged in to modify accounts.";
    header("Location: ../resource/page/login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $admin_id = (int)$_GET['id'];
    $new_status = $_GET['status'] === 'active' ? 'active' : 'inactive'; // Sanitize input
    $action = $new_status === 'inactive' ? 'Deactivate Account' : 'Activate Account';
    $description = $new_status === 'inactive' ? 'Admin deactivated another admin account' : 'Admin activated another admin account';

    // Update the admin status
    $updateQuery = "UPDATE admin SET status = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("si", $new_status, $admin_id);

    if ($stmt->execute()) {
        // Log the action
        $logQuery = "INSERT INTO AuditLog (admin_id, action, description, timestamp) 
                     VALUES (?, ?, ?, NOW())";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("iss", $_SESSION['admin_id'], $action, $description);
        $logStmt->execute();
        $logStmt->close();

        $_SESSION['success'] = "Admin account " . ($new_status === 'inactive' ? 'deactivated' : 'activated') . " successfully.";
    } else {
        $_SESSION['error'] = "Error updating account status: " . $conn->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: ../resource/layout/web-layout.php?page=account");
exit();
$conn->close();
