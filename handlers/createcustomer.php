<?php
session_start();
include '../database/database.php';

// Define MEMBER_TYPE_ID (adjust based on your database)
define('MEMBER_TYPE_ID', 1); // Assuming 'Member' type_id is 1

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $address = $_POST['address'] ?? '';
    $customertype = $_POST['customertype'] ?? '';
    $membership_ids = $_POST['membership_ids'] ?? '';
    $createdbyid = $_SESSION['admin_id'] ?? 0; // Adjust based on your session logic
    $createdate = date('Y-m-d H:i:s');

    // Override customertype to "Member" if membership_ids is provided
    if (!empty($membership_ids)) {
        $customertype = MEMBER_TYPE_ID;
    }

    // Insert into Customer table
    $sql = "INSERT INTO Customer (name, contact, address, type_id, createdbyid, createdate) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiss", $name, $contact, $address, $customertype, $createdbyid, $createdate);

    if ($stmt->execute()) {
        $customer_id = $conn->insert_id;

        // Insert membership IDs if provided
        if (!empty($membership_ids)) {
            $ids = array_filter(array_map('trim', explode(',', $membership_ids))); // Split by comma and clean
            foreach ($ids as $id) {
                $sql = "INSERT INTO membership (customer_id, membership_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $customer_id, $id);
                $stmt->execute();
            }
        }

        header("Location: ../resource/layout/web-layout.php?page=customer" );
        exit();
    } else {
        // Return error (you might want to redirect with an error message)
        die("Error creating customer: " . $conn->error);
    }
} else {
    // Invalid request method
    http_response_code(405);
    die("Method Not Allowed");
}

$conn->close();
?>