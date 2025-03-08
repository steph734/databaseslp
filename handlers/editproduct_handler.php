<?php
session_start();
include '../database/database.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/products.php?error=unauthorized");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $unitofmeasurement = $_POST['unitofmeasurement'];
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $updatedbyid = $_SESSION['admin_id'];

    $stmt = $conn->prepare("CALL UpdateProduct(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isidssii", $product_id, $product_name, $quantity, $price, $unitofmeasurement, $category_id, $supplier_id, $updatedbyid);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating product: " . $conn->error;
    }

    $stmt->close();
    $conn->close();


    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
}
