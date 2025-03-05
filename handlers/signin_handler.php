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


    if (empty($phone)) {
        die("Phone number is required.");
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
} else {

    echo "Invalid request method.";
}

$conn->close();