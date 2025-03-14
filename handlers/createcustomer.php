<?php
session_start();
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $address = $_POST['address'] ?? '';
    $customertype = $_POST['customertype'] ?? '';
    $createdbyid = $_SESSION['admin_id'] ?? 1; // Assuming admin_id is stored in session

    // Define type IDs
    $MEMBER_TYPE_ID = '1';
    $REGULAR_TYPE_ID = '2';

    // If customertype is Member, check for membership
    if ($customertype === $MEMBER_TYPE_ID) {
        $membershipCheck = $conn->query("SELECT membership_id FROM membership WHERE customer_id IS NULL LIMIT 1");
        if ($membershipCheck->num_rows == 0) {
            // No membership exists, default to Regular
            $customertype = $REGULAR_TYPE_ID;
            $name = ''; // Clear fields for Regular
            $contact = '';
            $address = '';
        }
    }

    // Insert customer with only createdate and createdbyid set, leaving updatedate and updatedbyid as NULL
    $stmt = $conn->prepare("INSERT INTO Customer (name, contact, address, type_id, createdbyid, createdate) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssis", $name, $contact, $address, $customertype, $createdbyid); // Adjusted bind_param types

    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=customer");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>