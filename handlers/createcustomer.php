<?php
include '../database/database.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $customertype = intval($_POST['customertype']); // Ensure it's an integer
    $createdbyid = 1; // Example, should be set dynamically from session
    $createdate = date("Y-m-d H:i:s");

    // Validate required fields
    if (empty($name) || empty($contact) || empty($address) || empty($customertype)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../resource/layout/web-layout.php?page=customer"); // Redirect back
        exit();
    }

    // Prepare SQL query
    $stmt = $conn->prepare("INSERT INTO customer (name, contact, address, type_id, createdbyid, createdate) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $name, $contact, $address, $customertype, $createdbyid, $createdate);

    // Execute the query
    if ($stmt->execute()) {
        $_SESSION['success'] = "Member added successfully!";
    } else {
        $_SESSION['error'] = "Error adding member: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the customer page
    header("Location: ../resource/layout/web-layout.php?page=customer");
    exit();
}
?>
