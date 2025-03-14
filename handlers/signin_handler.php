<?php
include '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username already exists
    $check_sql = "SELECT username FROM Admin WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Username already exists, redirect back with error
        header("Location: ../resource/signup.php?error=username_taken");
        exit();
    }

    // Proceed with insertion if username is unique
    if (empty($phone)) {
        header("Location: ../resource/signup.php?error=phone_required");
        exit();
    }

    $sql = "INSERT INTO Admin (first_name, middle_name, last_name, username, password, email, phonenumber, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $first_name, $middle_name, $last_name, $username, $password, $email, $phone, $role);

    if ($stmt->execute()) {
        header("Location: ../resource/login.php?success=true");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $check_stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
