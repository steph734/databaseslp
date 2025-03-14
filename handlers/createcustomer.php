<?php
include '../database/database.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $customertype = intval($_POST['customertype']); // Ensure it's an integer
    $createdbyid = $_SESSION['admin_id'];

    // Validate required fields
    if (empty($name) || empty($contact) || empty($address) || empty($customertype)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../resource/layout/web-layout.php?page=customer"); // Redirect back
        exit();
    }

    // Prepare SQL query with both createdate and updatedate set to NOW()
    $stmt = $conn->prepare("INSERT INTO customer (name, contact, address, type_id, createdbyid, createdate, updatedate) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("sssii", $name, $contact, $address, $customertype, $createdbyid);

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