<?php
session_start();
include '../../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sales_id = $_POST['sales_id'];
    $sale_date = $_POST['sale_date'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $subtotal_price = $_POST['subtotal_price'];
    $total_amount = $_POST['total_amount'];
    $updatedbyid = $_SESSION['admin_id'] ?? 1; // Assuming admin_id is in session

    try {
        $conn->begin_transaction();

        // Update Sales table
        $stmt = $conn->prepare("UPDATE Sales SET sale_date = ?, total_amount = ?, updatedbyid = ? WHERE sales_id = ?");
        $stmt->bind_param("sdii", $sale_date, $total_amount, $updatedbyid, $sales_id);
        $stmt->execute();

        // Update SalesLine table
        $stmt = $conn->prepare("UPDATE SalesLine SET product_id = ?, quantity = ?, unit_price = ?, subtotal_price = ? WHERE sales_id = ?");
        $stmt->bind_param("iiddi", $product_id, $quantity, $unit_price, $subtotal_price, $sales_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success'] = "Sale updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating sale: " . $e->getMessage();
    }

    $stmt->close();
    $conn->close();
    header("Location: ../resource/layout/web-layout.php?page=sales");
    exit();
}
