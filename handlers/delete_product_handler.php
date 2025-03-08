<?php
session_start();
include '../database/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../views/products.php?error=unauthorized");
    exit();
}

// Check if request method is GET and 'id' is set
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM Product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting product.";
    }

    // Close statement & connection
    $stmt->close();
    $conn->close();

    // Redirect back to product page
    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
}
