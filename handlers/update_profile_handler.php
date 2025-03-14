<?php
session_start();
include '../database/database.php';

// Ensure the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "You must be logged in to update your profile.";
    header("Location: ../resource/layout/web-layout.php?page=profileadmin");
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $middle_name = $conn->real_escape_string($_POST['middle_name'] ?? '');
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $phonenumber = $conn->real_escape_string($_POST['phonenumber']);

    // Check if the username is already taken by another admin
    $checkQuery = "SELECT admin_id FROM admin WHERE username = ? AND admin_id != ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("si", $username, $admin_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = "Username '$username' is already taken. Please choose a different username.";
        header("Location: ../resource/layout/web-layout.php?page=profileadmin");
        exit();
    }
    $checkStmt->close();

    // Update the profile
    $updateQuery = "UPDATE admin 
                    SET first_name = ?, middle_name = ?, last_name = ?, username = ?, email = ?, phonenumber = ? 
                    WHERE admin_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $username, $email, $phonenumber, $admin_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully.";
        $_SESSION['username'] = $username; // Update session username

        // Log the action
        $logQuery = "INSERT INTO AuditLog (admin_id, action, description, timestamp) 
                     VALUES (?, 'Update Profile', 'Admin updated their profile details', NOW())";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("i", $admin_id);
        $logStmt->execute();
        $logStmt->close();
    } else {
        $_SESSION['error'] = "Error updating profile: " . $conn->error;
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: ../resource/layout/web-layout.php?page=profileadmin");
exit();
$conn->close();
