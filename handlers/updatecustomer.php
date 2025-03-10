<?php
include '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    $customer_id = $_POST['customer_id'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $is_member = $_POST['is_member'];
    $customertype = $_POST['customertype'];
    $updatedbyid = $_SESSION['admin_id'] ?? NULL; // Default to 1 if session admin ID is not set

    $query = "UPDATE Customer SET name=?, contact=?, address=?, is_member=?, type_id=?, updatedbyid=?, updatedate=NOW() WHERE customer_id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssiii", $name, $contact, $address, $is_member, $customertype, $updatedbyid, $customer_id);
    
    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=customer");
        exit();
    } else {
        header("Location: ../resource/layout/web-layout.php?error=Failed to update customer");
        exit();
    }
}
?>
