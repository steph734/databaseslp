<?php
include '../database/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/inventory.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $quantity_to_add = $_POST['quantity_to_add'] ?? 0;
    $received_date = $_POST['received_date'] ?? date('Y-m-d');
    $createdbyid = $_SESSION['admin_id'];

    // Validate inputs
    if ($product_id <= 0 || $price < 0 || $quantity_to_add <= 0) {
        $_SESSION['error'] = "Invalid input values.";
        header("Location: ../resource/views/inventory.php?error=invalid_input");
        exit();
    }

    // Prepare stored procedure call
    $stmt = $conn->prepare("CALL UpdateInventoryStock(?, ?, ?, ?, ?)");
    $stmt->bind_param("idisi", $product_id, $price, $quantity_to_add, $received_date, $createdbyid);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Inventory updated successfully!";
        header("Location: ../resource/layout/web-layout.php?page=inventory");
    } else {
        $_SESSION['error'] = "Failed to update inventory.";
        header("Location: ../resource/layout/web-layout.php?page=inventory");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=inventory");
}

$conn->close();
