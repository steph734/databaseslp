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
    $quantity = (int)($_POST['quantity'] ?? 0); // Default to 0, allow override if provided
    $supplier_id = $_POST['supplier_id'] ?? NULL; // Optional supplier
    $createdbyid = $_SESSION['admin_id'];

    // Validate required fields
    if (empty($product_name) || empty($unitofmeasurement) || empty($category_id)) {
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../resource/layout/web-layout.php?page=products&error=missing_fields");
        exit();
    }

    try {
        // Enable MySQLi Exception Mode
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $stmt = $conn->prepare("CALL AddProduct(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sidssii", $product_name, $quantity, $price, $unitofmeasurement, $category_id, $supplier_id, $createdbyid);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Product added successfully with quantity $quantity!";
        } else {
            $_SESSION['error'] = "Failed to add product: " . $stmt->error;
        }
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Error adding product: " . $e->getMessage();
    }

    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();

    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=products&error=invalid_request");
    exit();
}
