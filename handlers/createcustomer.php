<?php
session_start();
include '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? NULL;
    $contact = $_POST['contact'] ?? NULL;
    $address = $_POST['address'] ?? NULL;
    $is_member = isset($_POST['is_member']) ? 1 : 0;
    $type_id = $_SESSION['type_id'] ?? NULL;
    $createdbyid = $_SESSION['admin_id'] ?? NULL; 

    try {
        $stmt = $conn->prepare("CALL add_customer(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $name, $contact, $address, $is_member, $type_id, $createdbyid);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Customer added successfully!";
        } else {
            $_SESSION['error'] = "Error adding customer.";
        }

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        $_SESSION['error'] = "Exception: " . $e->getMessage();
    }
}

header("Location: ../resource/layout/web-layout.php?page=customer");
exit();