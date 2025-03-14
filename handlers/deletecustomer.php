<?php
include '../database/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $customer_ids = $input['customer_ids'] ?? [];

    if (empty($customer_ids)) {
        echo json_encode(['success' => false, 'error' => 'No customers selected']);
        exit;
    }

    // Prepare a safe query to delete multiple IDs
    $placeholders = implode(',', array_fill(0, count($customer_ids), '?'));
    $stmt = $conn->prepare("DELETE FROM Customer WHERE customer_id IN ($placeholders)");
    
    // Bind the customer IDs dynamically
    $stmt->bind_param(str_repeat('i', count($customer_ids)), ...$customer_ids);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

$conn->close();
?>