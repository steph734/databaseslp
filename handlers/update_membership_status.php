<?php
session_start();
require_once '../database/database.php';

header('Content-Type: text/plain');

try {
    if (!isset($_POST['membership_id'], $_POST['status'])) {
        throw new Exception("Missing required fields");
    }

    $membership_id = $_POST['membership_id'];
    $status = $_POST['status'];
    $updated_by = $_SESSION['user_id'] ?? 'admin'; // Replace with actual user ID
    $updated_date = date('Y-m-d H:i:s');

    $query = "UPDATE membership SET status = ?, updatedbyid = ?, updatedate = ? WHERE membership_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $status, $updated_by, $updated_date, $membership_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo "error: " . $e->getMessage();
}
?>