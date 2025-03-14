<?php
session_start();
include '../database/database.php';
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/products.php?error=unauthorized");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $product_id = $data['product_id'] ?? null;
    if (!$product_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No product ID provided.']);
        exit();
    }
    $admin_id = $_SESSION['admin_id'];
    $stmt = $conn->prepare("UPDATE Product SET status = 'available', updatedbyid = ?, updatedate = NOW() WHERE product_id = ?");
    $stmt->bind_param("ii", $admin_id, $product_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Failed to mark product as available or product not found.']);
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: ../resource/layout/web-layout.php?page=products");
}
exit();
