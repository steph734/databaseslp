<?php
include '../database/database.php';
header('Content-Type: application/json');

$product_id = $_GET['product_id'] ?? '';

if ($product_id) {
    $query = "SELECT stock_quantity, price FROM Inventory WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'exists' => true,
            'stock_quantity' => $row['stock_quantity'],
            'price' => $row['price']
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'No product ID provided']);
}

$conn->close();
