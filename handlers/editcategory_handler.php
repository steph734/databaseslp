<?php
include '../database/database.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/products.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = $_POST['category_id'] ?? '';
    $new_category_id = $_POST['new_category_id'] ?? '';
    $category_name = $_POST['category_name'] ?? '';
    $updatedbyid = $_SESSION['admin_id'];

    // Ensure category_id and new_category_id are valid
    if (empty($category_id) || empty($new_category_id) || empty($category_name)) {
        $_SESSION['error'] = "Both Category IDs and Category Name are required.";
        header("Location: ../resource/layout/web-layout.php?page=products&error=missing_fields");
        exit();
    }

    // Prevent '0' from being used as an ID
    if ($new_category_id == '0') {
        $_SESSION['error'] = "Invalid category ID.";
        header("Location: ../resource/layout/web-layout.php?page=products&error=invalid_id");
        exit();
    }

    // Call the stored procedure
    $stmt = $conn->prepare("CALL UpdateCategory(?, ?, ?, ?)");
    $stmt->bind_param("sssi", $category_id, $new_category_id, $category_name, $updatedbyid);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Category updated successfully!";
        header("Location: ../resource/layout/web-layout.php?page=products");
    } else {
        $_SESSION['error'] = "Failed to update category: " . $stmt->error;
        header("Location: ../resource/layout/web-layout.php?page=products&error=database_error");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=products&error=invalid_request");
}

$conn->close();
