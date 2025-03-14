<?php
session_start();
include '../database/database.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "You must be logged in to deactivate your account.";
    header("Location: ../resource/layout/web-layout.php?page=profileadmin");
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate'])) {
    // Delete the admin account (or update a status column if preferred)
    $deleteQuery = "DELETE FROM admin WHERE admin_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $admin_id);

    if ($stmt->execute()) {
        // Log the action before logout
        $updateQuery = "UPDATE admin SET status = 'inactive' WHERE admin_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("i", $admin_id);
        $logStmt->execute();
        $logStmt->close();

        session_unset();
        session_destroy();
        $_SESSION['success'] = "Account deactivated successfully.";
        header("Location: ../resource/layout/web-layout.php?page=profileadmin");
    } else {
        $_SESSION['error'] = "Error deactivating account: " . $conn->error;
        header("Location: ../resource/layout/web-layout.php?page=profileadmin");
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../resource/layout/web-layout.php?page=profileadmin");
}

exit();
$conn->close();
