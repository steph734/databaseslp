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
    $quantity = $_POST['quantity'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $unitofmeasurement = $_POST['unitofmeasurement'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? NULL;
    $createdbyid = $_SESSION['admin_id'];


    if (empty($product_name) || empty($unitofmeasurement) || empty($category_id)) {
        $_SESSION['error'] = "All fields are required except Supplier ID.";
        header("Location: ../resource/views/products.php?error=missing_fields");
        exit();
    }


    $stmt = $conn->prepare("CALL AddProduct(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidssii", $product_name, $quantity, $price, $unitofmeasurement, $category_id, $supplier_id, $createdbyid);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully!";
        header("Location: ../resource/layout/web-layout.php?page=products");
    } else {
        $_SESSION['error'] = "Failed to add product.";
        header("Location: ../resource/layout/web-layout.php?page=products");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=products");
}

$conn->close();