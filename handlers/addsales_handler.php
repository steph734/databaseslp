<?php
session_start();
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
    $sale_date = $_POST['sale_date'];
    $payment_method = $_POST['payment_method'];
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $unit_prices = $_POST['unit_price'];

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Insert into Sales table
        $stmt = $conn->prepare("INSERT INTO Sales (customer_id, sale_date, payment_method, total_amount, createdate) 
            VALUES (?, ?, ?, ?, NOW())");
        
        $total_amount = 0;
        for ($i = 0; $i < count($product_ids); $i++) {
            $total_amount += $quantities[$i] * $unit_prices[$i];
        }
        
        $stmt->bind_param("sssd", $customer_id, $sale_date, $payment_method, $total_amount);
        $stmt->execute();
        $sales_id = $conn->insert_id;

        // Insert into SalesLine table
        $stmt = $conn->prepare("INSERT INTO SalesLine (sales_id, product_id, quantity, unit_price, subtotal_price) 
            VALUES (?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < count($product_ids); $i++) {
            $subtotal = $quantities[$i] * $unit_prices[$i];
            $stmt->bind_param("iiidd", $sales_id, $product_ids[$i], $quantities[$i], $unit_prices[$i], $subtotal);
            $stmt->execute();
            
            // Update inventory
            $conn->query("UPDATE Inventory SET stock_quantity = stock_quantity - {$quantities[$i]} 
                WHERE product_id = {$product_ids[$i]}");
        }

        $conn->commit();
        $_SESSION['success'] = "Sale added successfully!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error adding sale: " . $e->getMessage();
    }
    
    header("Location: ../resource/layout/web-layout.php?page=sales");
    exit();
}
?>