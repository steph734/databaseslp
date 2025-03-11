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

    // Check basic input validation
    if ($product_id <= 0 || $price < 0 || $quantity_to_add <= 0) {
        $_SESSION['error'] = "Check your inputs: Product, Price, and Quantity must be more than zero!";
        header("Location: ../resource/layout/web-layout.php?page=inventory&error=invalid");
        exit();
    }

    // Check product quantity limit
    $productQuery = "SELECT quantity FROM Product WHERE product_id = ?";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $productResult = $stmt->get_result();
    $productRow = $productResult->fetch_assoc();
    $maxQuantity = $productRow['quantity'] ?? 0;
    $stmt->close();

    // Check current inventory stock
    $inventoryQuery = "SELECT stock_quantity FROM Inventory WHERE product_id = ?";
    $stmt = $conn->prepare($inventoryQuery);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $inventoryResult = $stmt->get_result();
    $currentStock = $inventoryResult->num_rows > 0 ? $inventoryResult->fetch_assoc()['stock_quantity'] : 0;
    $stmt->close();

    // Validate against max quantity
    if (($currentStock + $quantity_to_add) > $maxQuantity) {
        $_SESSION['error'] = "Quantity to add exceeds available stock in Product table (Max: $maxQuantity, Current: $currentStock)!";
        header("Location: ../resource/layout/web-layout.php?page=inventory&error=exceed_limit");
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

$conn->close();
exit();
