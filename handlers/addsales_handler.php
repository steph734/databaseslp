<?php
session_start();
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
    $sale_date = $_POST['sale_date'];
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];
    $unit_prices = $_POST['unit_price'];
    $payment_method = $_POST['payment_method'];
    $createdbyid = 1; // Replace with actual logged-in admin ID

    $conn->begin_transaction();

    try {
        // Calculate total amount and check stock
        $total_amount = 0;
        $errors = [];

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $quantity = $quantities[$i];
            $unit_price = $unit_prices[$i];
            $subtotal_price = $quantity * $unit_price;
            $total_amount += $subtotal_price;

            // Check stock in Inventory
            $stmt = $conn->prepare("SELECT stock_quantity FROM Inventory WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if (!$row || $row['stock_quantity'] < $quantity) {
                $errors[] = "Insufficient stock for Product ID $product_id. Available: " . ($row ? $row['stock_quantity'] : 0);
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode("; ", $errors));
        }

        // Insert into Sales
        $stmt = $conn->prepare("INSERT INTO Sales (customer_id, sale_date, total_amount, payment_method, createdbyid) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsi", $customer_id, $sale_date, $total_amount, $payment_method, $createdbyid);
        $stmt->execute();
        $sales_id = $conn->insert_id;

        // Insert into SalesLine and update Inventory
        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $quantity = $quantities[$i];
            $unit_price = $unit_prices[$i];
            $subtotal_price = $quantity * $unit_price;

            // Insert into SalesLine
            $stmt = $conn->prepare("INSERT INTO SalesLine (sales_id, product_id, quantity, unit_price, subtotal_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidd", $sales_id, $product_id, $quantity, $unit_price, $subtotal_price);
            $stmt->execute();

            // Deduct from Inventory
            $stmt = $conn->prepare("UPDATE Inventory SET stock_quantity = stock_quantity - ?, updatedbyid = ?, updatedate = NOW() WHERE product_id = ?");
            $stmt->bind_param("iii", $quantity, $createdbyid, $product_id);
            $stmt->execute();
        }

        $conn->commit();
        $_SESSION['success'] = "Sale added successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Failed to add sale: " . $e->getMessage();
    }

    header("Location: ../resource/layout/web-layout.php?page=sales");
    exit();
}
