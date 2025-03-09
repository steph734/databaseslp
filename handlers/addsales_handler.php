<?php
session_start();
include '../../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_POST['customer_id'];
    $sale_date = $_POST['sale_date'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $subtotal_price = $quantity * $unit_price;
    $total_amount = $subtotal_price; // Simplify for single line; adjust for multiple lines
    $createdbyid = $_SESSION['admin_id'] ?? 1; // Assuming admin_id is in session

    try {
        // Start transaction
        $conn->begin_transaction();

        // Insert into Sales
        $stmt = $conn->prepare("INSERT INTO Sales (customer_id, sale_date, total_amount, createdbyid) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isdi", $customer_id, $sale_date, $total_amount, $createdbyid);
        $stmt->execute();
        $sales_id = $conn->insert_id;

        // Insert into SalesLine
        $stmt = $conn->prepare("INSERT INTO SalesLine (sales_id, product_id, quantity, unit_price, subtotal_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $sales_id, $product_id, $quantity, $unit_price, $subtotal_price);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success'] = "Sale added successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error adding sale: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();
    header("Location: ../resource/layout/web-layout.php?page=sales");
    exit();
}
