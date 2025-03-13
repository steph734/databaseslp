<?php
session_start();
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $product_ids = $data['product_ids'] ?? [];

    if (empty($product_ids)) {
        $_SESSION['error'] = 'No products selected for deletion.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Sanitize product IDs
    $product_ids = array_map('intval', $product_ids);

    try {
        // Prepare the deletion query
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $query = "DELETE FROM Product WHERE product_id IN ($placeholders)";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare query: ' . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Selected products deleted successfully.';
        } else {
            throw new Exception('Failed to delete products: ' . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }

    $conn->close();
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    $_SESSION['error'] = 'Invalid request method.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>