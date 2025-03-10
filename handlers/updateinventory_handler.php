<?php
include '../database/database.php';
session_start();

// Make sure an admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "You need to log in!";
    header("Location: ../resource/views/inventory.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $product_id = $_POST['product_id'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $quantity_to_add = $_POST['quantity_to_add'] ?? 0;
    $received_date = $_POST['received_date'] ?? date('Y-m-d');
    $createdbyid = $_SESSION['admin_id'];

    // Check if inputs are okay
    if ($product_id <= 0 || $price < 0 || $quantity_to_add <= 0) {
        $_SESSION['error'] = "Check your inputs: Product, Price, and Quantity must be more than zero!";
        header("Location: ../resource/layout/web-layout.php?page=inventory&error=invalid");
        exit();
    }

    // Call the stored procedure
    $stmt = $conn->prepare("CALL UpdateInventoryStock(?, ?, ?, ?, ?)");
    $stmt->bind_param("idisi", $product_id, $price, $quantity_to_add, $received_date, $createdbyid);

    // Try to run it
    if ($stmt->execute()) {
        $_SESSION['success'] = "Inventory updated!";
        header("Location: ../resource/layout/web-layout.php?page=inventory");
    } else {
        $_SESSION['error'] = "Something failed: " . $stmt->error;
        header("Location: ../resource/layout/web-layout.php?page=inventory&error=failed");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Wrong request type!";
    header("Location: ../resource/layout/web-layout.php?page=inventory&error=wrong_request");
}

<<<<<<< HEAD
$conn->close();
=======
$conn->close();
>>>>>>> 3e1a7cf36debfc72d0a4b43a979122b10df7cd12
