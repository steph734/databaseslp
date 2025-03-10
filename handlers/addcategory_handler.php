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

    // Check if the category ID already exists
    $checkStmt = $conn->prepare("SELECT category_id FROM Category WHERE category_id = ?");
    $checkStmt->bind_param("s", $category_id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $_SESSION['error'] = "ID already exist. Please try again.";
        header("Location: ../resource/layout/web-layout.php?page=products");
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    // Insert new category
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