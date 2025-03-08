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
    $product_id = $_POST['product_id'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $total_value = $_POST['total_value'];
    $received_date = $_POST['received_date'];
    $last_restock_date = $_POST['last_restock_date'];
    $damage_stock = $_POST['damage_stock'];
    $createdbyid = 1; // Change this to the actual user ID if needed

    // Insert into database
    $query = "INSERT INTO Inventory (product_id, price, stock_quantity, total_value, received_date, last_restock_date, damage_stock, createdbyid)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ididdsii", $product_id, $price, $stock_quantity, $total_value, $received_date, $last_restock_date, $damage_stock, $createdbyid);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Inventory added successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the inventory page
    header("Location: ../resource/layout/web-layout.php?page=inventory");
    exit();
}
?>
