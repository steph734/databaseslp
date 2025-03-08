<?php
include '../database/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inventory_id = $_POST['inventory_id'];
    $product_id = $_POST['product_id'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $total_value = $_POST['total_value'] ?: 'NULL';
    $received_date = $_POST['received_date'] ?: 'NULL';
    $last_restock_date = $_POST['last_restock_date'] ?: 'NULL';
    $damage_stock = $_POST['damage_stock'] ?: 'NULL';
    $updated_by = 1; // Change this based on logged-in user

    $query = "UPDATE Inventory 
              SET product_id='$product_id', price='$price', stock_quantity='$stock_quantity', 
                  total_value=$total_value, received_date='$received_date', 
                  last_restock_date='$last_restock_date', damage_stock='$damage_stock', 
                  updatedbyid='$updated_by', updatedate=NOW() 
              WHERE inventory_id='$inventory_id'";

    if ($conn->query($query)) {
        session_start();
        $_SESSION['success'] = "Inventory updated successfully!";
    } else {
        session_start();
        $_SESSION['error'] = "Error: " . $conn->error;
    }

    header("Location: ../resource/layout/web-layout.php?page=inventory");
    exit();
}
?>