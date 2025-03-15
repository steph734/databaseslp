<?php
session_start();
include '../database/database.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $customerType = filter_input(INPUT_POST, 'customertype', FILTER_VALIDATE_INT);
    $updatedById = $_SESSION['admin_id'] ?? null; // Fetch admin_id from session

    // Check if all required fields are provided, including admin_id
    // if (!$customerId || empty($name) || empty($contact) || empty($address) || !$customerType || !$updatedById) {
    //     $_SESSION['error'] = "All fields are required, or you are not logged in as an admin.";
    //     header("Location: ../resource/layout/web-layout.php?page=customer");
    //     exit();
    // }

    try {
        // Prepare the SQL statement
        $query = "UPDATE Customer 
                  SET name = ?, contact = ?, address = ?, type_id = ?, 
                      updatedbyid = ?, updatedate = NOW() 
                  WHERE customer_id = ?";
        
        $stmt = $conn->prepare($query);
        
        // Bind parameters
        $stmt->bind_param("sssiii", $name, $contact, $address, $customerType, $updatedById, $customerId);
        
        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['success'] = "Customer updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update customer. Please try again.";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred: " . $e->getMessage();
    }
    
    // Close the database connection
    $conn->close();
    
    // Redirect back to the customer page
    header("Location: ../resource/layout/web-layout.php?page=customer");
    exit();
} else {
    // If accessed directly without POST, redirect to customer page
    header("Location: ../resource/layout/web-layout.php?page=customer");
    exit();
}
?>