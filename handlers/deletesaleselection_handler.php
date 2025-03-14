<?php
session_start();
include '../database/database.php';

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Optional: Check if user is authorized (e.g., admin)
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Get sales IDs from POST data
$ids = $_POST['ids'] ?? '';
if (empty($ids)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No sales IDs provided']);
    exit;
}

// Sanitize sales IDs
$salesIds = array_map('intval', explode(',', $ids));
$salesIdsStr = implode(',', $salesIds);

try {
    // Begin transaction for atomicity
    $conn->begin_transaction();

    // Delete from SalesLine first (due to foreign key dependency)
    $deleteSalesLineQuery = "DELETE FROM SalesLine WHERE sales_id IN ($salesIdsStr)";
    if (!$conn->query($deleteSalesLineQuery)) {
        throw new Exception("Failed to delete SalesLine records: " . $conn->error);
    }

    // Delete from Sales
    $deleteSalesQuery = "DELETE FROM Sales WHERE sales_id IN ($salesIdsStr)";
    if (!$conn->query($deleteSalesQuery)) {
        throw new Exception("Failed to delete Sales records: " . $conn->error);
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
exit;
