<?php

include '../database/database.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $middle_name = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
    $last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : ''; 
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';

 
    if (empty($phone)) {
        die("Phone number is required.");
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO Admin (first_name, middle_name, last_name, username, password, email, phonenumber, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Bind parameters (use the correct variable names)
    $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $username, $password, $email, $phone, $role);

    // Execute the statement
    if ($stmt->execute()) {
   
        header("Location: ../resource/login.php?success=1"); 
        exit();
    } else {

        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
   
    echo "Invalid request method.";
}

$conn->close();
?>