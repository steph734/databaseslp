<?php
session_start();
include '../database/database.php';


if (isset($_POST['submit'])) {
    // Get form data
    $membership_id = $_POST['membershipid'];
    $status = $_POST['status'];
    $date_renewal = $_POST['daterenewal'];
    $createdbyid = $_SESSION['admin_id'];
    $created_date = date('Y-m-d H:i:s'); // Current timestamp

    // Input validation
    if (empty($membership_id) || empty($status) || empty($date_renewal)) {
        // Redirect back with error message
        header("Location: ../membership.php?error=All fields are required");
        exit();
    }

    // Prepare SQL statement
    $sql = "INSERT INTO membership (membership_id, status, date_renewal, createdbyid, createdate, updatedate) 
            VALUES (?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("sisss", $membership_id, $status, $date_renewal, $created_by_id, $created_date);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Success - redirect back to membership page
            header("Location: ../resource/layout/web-layout.php?page=membership");
        } else {
            // Error executing query
            header("Location: ../membership.php?error=Error creating member: " . $conn->error);
        }
        
        $stmt->close();
    } else {
        // Error preparing statement
        header("Location: ../membership.php?error=Database error: " . $conn->error);
    }
    
    $conn->close();
} else {
    // If not submitted through form, redirect back
    header("Location: ../membership.php");
}

exit();
?>