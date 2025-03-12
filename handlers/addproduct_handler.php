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
    $product_name = $_POST['product_name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $unitofmeasurement = $_POST['unitofmeasurement'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $createdbyid = $_SESSION['admin_id'];
    $quantity = 0; // Default quantity
    $supplier_id = NULL; // Supplier is not set here

    // Validate required fields
    if (empty($product_name) || empty($unitofmeasurement) || empty($category_id)) {
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../resource/views/products.php?error=missing_fields");
        exit();
    }

    // Call the stored procedure to add the product
    $stmt = $conn->prepare("CALL AddProduct(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidssii", $product_name, $quantity, $price, $unitofmeasurement, $category_id, $supplier_id, $createdbyid);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully with quantity 0!";
        header("Location: ../resource/layout/web-layout.php?page=products");
    } else {
        $_SESSION['error'] = "Failed to add product: " . $stmt->error;
        header("Location: ../resource/layout/web-layout.php?page=products&error=database_error");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=products&error=invalid_request");
}

$conn->close();