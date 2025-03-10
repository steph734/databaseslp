<?php
include '../database/database.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    
    $membership_id = $_POST['membership_id'];
    $status = $_POST['status'];
    $startdate = $_POST['startdate'];
    $daterenewal = $_POST['daterenewal'];
    $updatedbyid = $_SESSION['admin_id'] ?? NULL; // Get admin ID from session
    
    $query = "UPDATE membership SET status = ?, date_start = ?, date_renewal = ?, updatedbyid = ?, updatedate = NOW() WHERE membership_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssii", $status, $startdate, $daterenewal, $updatedbyid, $membership_id);
    
    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=membership");
        exit();
    } else {
        header("Location: ../resource/layout/web-layout.php?error=Failed to update membership");
        exit();
    }
} else {
    header("Location: ../../pages/membership.php?error=Invalid request");
    exit();
}
