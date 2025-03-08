<?php
include '../database/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/products.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $category_id = $_POST['category_id'] ?? '';
    $category_name = $_POST['category_name'] ?? '';
    $createdbyid = $_SESSION['admin_id'];

    // Validate inputs
    if (empty($category_id) || empty($category_name)) {
        $_SESSION['error'] = "Category ID and Category Name are required.";
        header("Location: ../resource/layout/web-layout.php?page=products&error=missing_fields");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO Category (category_id, category_name, createdbyid) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $category_id, $category_name, $createdbyid);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Category '$category_name' added successfully with ID $category_id!";
        header("Location: ../resource/layout/web-layout.php?page=products");
    } else {
        $_SESSION['error'] = "Failed to add category: " . $stmt->error;
        header("Location: ../resource/layout/web-layout.php?page=products&error=database_error");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=products&error=invalid_request");
}

$conn->close();
