<?php
session_start();
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get form data
$customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
$sale_date = $_POST['sale_date'] ?? date('Y-m-d');
$payment_method = $_POST['payment_method'] ?? '';
$product_ids = $_POST['product_id'] ?? [];
$quantities = $_POST['quantity'] ?? [];
$unit_prices = $_POST['unit_price'] ?? [];

if (empty($product_ids) || empty($quantities) || empty($unit_prices) || count($product_ids) !== count($quantities) || count($product_ids) !== count($unit_prices)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Missing or mismatched product data']);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Calculate total amount
    $total_amount = 0;
    $deductions = [];
    for ($i = 0; $i < count($product_ids); $i++) {
        $quantity = intval($quantities[$i]);
        $unit_price = floatval($unit_prices[$i]);
        $total_amount += $quantity * $unit_price;

        // Validate stock availability
        $stock_query = "SELECT stock_quantity FROM Inventory WHERE product_id = ?";
        $stmt = $conn->prepare($stock_query);
        $stmt->bind_param('i', $product_ids[$i]);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $current_stock = $row['stock_quantity'] ?? 0;
        $stmt->close();

        if ($quantity > $current_stock) {
            throw new Exception("Insufficient stock for Product ID: {$product_ids[$i]}");
        }

        $deductions[] = [
            'product_id' => $product_ids[$i],
            'quantity_deducted' => $quantity
        ];
    }

    // Insert into Sales table
    $stmt = $conn->prepare("INSERT INTO Sales (customer_id, sale_date, payment_method, total_amount, createdate) 
        VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssd", $customer_id, $sale_date, $payment_method, $total_amount);
    $stmt->execute();
    $sales_id = $conn->insert_id;
    $stmt->close();

    // Insert into SalesLine and update Inventory
    $stmt = $conn->prepare("INSERT INTO SalesLine (sales_id, product_id, quantity, unit_price, subtotal_price) 
        VALUES (?, ?, ?, ?, ?)");
    $inventory_stmt = $conn->prepare("UPDATE Inventory SET stock_quantity = stock_quantity - ? WHERE product_id = ?");

    for ($i = 0; $i < count($product_ids); $i++) {
        $subtotal = $quantities[$i] * $unit_prices[$i];
        $stmt->bind_param("iiidd", $sales_id, $product_ids[$i], $quantities[$i], $unit_prices[$i], $subtotal);
        $stmt->execute();

        // Update inventory
        $inventory_stmt->bind_param("ii", $quantities[$i], $product_ids[$i]);
        $inventory_stmt->execute();
    }

    $stmt->close();
    $inventory_stmt->close();

    // Commit transaction
    $conn->commit();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Sale added successfully!',
        'deductions' => $deductions
    ]);
} catch (Exception $e) {
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Error adding sale: ' . $e->getMessage()]);
}

$conn->close();
exit;
