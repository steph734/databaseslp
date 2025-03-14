<?php
session_start();
include '../database/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $product_ids = $data['product_ids'] ?? [];

    if (empty($product_ids)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No product IDs provided']);
        exit();
    }

    // Prepare the placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $conn->prepare("SELECT product_id, status FROM Product WHERE product_id IN ($placeholders)");

    // Bind the product IDs dynamically
    $types = str_repeat('i', count($product_ids)); // All product_ids are integers
    $stmt->bind_param($types, ...$product_ids);

    $stmt->execute();
    $result = $stmt->get_result();
    $statuses = [];
    while ($row = $result->fetch_assoc()) {
        $statuses[] = [
            'product_id' => $row['product_id'],
            'status' => $row['status']
        ];
    }

    $stmt->close();
    header('Content-Type: application/json');
    echo json_encode($statuses);
    exit();
}

header('Content-Type: application/json');
echo json_encode(['error' => 'Invalid request method']);
exit();
