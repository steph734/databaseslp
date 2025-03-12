<?php
session_start();
include '../database/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $product_ids = $data['product_ids'] ?? [];

    if (empty($product_ids)) {
        $response['message'] = 'No products selected for deletion.';
        echo json_encode($response);
        exit;
    }

    // Prepare the SQL query to delete multiple products
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $query = "DELETE FROM Product WHERE product_id IN ($placeholders)";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        // Bind the product IDs to the query
        $stmt->bind_param(str_repeat('s', count($product_ids)), ...$product_ids);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
            // $response['message'] = 'Selected products deleted successfully.';
            $_SESSION['success'] = 'Selected products deleted successfully.';
        } else {
            $response['message'] = 'Failed to delete products: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Failed to prepare query: ' . $conn->error;
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
$conn->close();
?>