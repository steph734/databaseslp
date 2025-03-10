<?php
include '../database/database.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status']?? NULL;
    $start_date = $_POST['startdate']?? NULL;
    $date_renewal = $_POST['daterenewal']?? NULL;
    $createdbyid = $_SESSION['admin_id'];

    // Validate required fields
    if (empty($status)) {
        die("Status is required.");
    }

    // Prepare and bind statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO membership (status, date_start, date_renewal, createdbyid, createdate) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssi", $status, $start_date, $date_renewal, $createdbyid);

    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=membership");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
