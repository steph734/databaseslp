<?php
session_start();
include '../database/database.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Sanitize and validate input data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $customerType = filter_input(INPUT_POST, 'customertype', FILTER_VALIDATE_INT);
    $createdById = $_SESSION['admin_id'] ?? null; // Fetch admin_id from session

    // Check if all required fields are provided, including admin_id
//     if (empty($name) || empty($contact) || empty($address) || !$customerType || !$createdById) {
//         $_SESSION['error'] = "All fields are required, or you are not logged in as an admin.";
//         header("Location: ../resource/layout/web-layout.php?page=customer");
//         exit();
//   }



    try {
        // Prepare the SQL statement
        // Including updatedbyid and updatedate initialized with createdbyid and NOW()
        $query = "INSERT INTO Customer (name, contact, address, type_id, createdbyid, createdate, updatedbyid, updatedate) 
                  VALUES (?, ?, ?, ?, ?, NOW(), ?, NOW())";
        
        $stmt = $conn->prepare($query);
        
        // Bind parameters (updatedbyid is set to the same as createdbyid initially)
        $stmt->bind_param("sssiii", $name, $contact, $address, $customerType, $createdById, $createdById);
        
        // Execute the statement
        if ($stmt->execute()) {
            $_SESSION['success'] = "Customer created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create customer. Please try again.";
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