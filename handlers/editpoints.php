<?php
require_once '../database/database.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get POST data
    $points_id = $_POST['points_id'] ?? null;
    $membership_id = $_POST['membership_id'] ?? null;
    $sales_id = $_POST['sales_id'] ?? null;
    $total_purchase = floatval($_POST['total_purchase'] ?? 0);
    $points_amount = intval($_POST['points_amount'] ?? 0);

    // Validate required fields
    if (!$points_id || !$membership_id || !$sales_id || $total_purchase <= 0) {
        throw new Exception('All fields are required');
    }

    // Validate points calculation (10 points per 1000 pesos)
    $expected_points = floor($total_purchase / 1000) * 10;
    if ($points_amount !== $expected_points) {
        throw new Exception('Invalid points amount calculation');
    }

    // Prepare and execute update query
    $query = "UPDATE points SET membership_id = ?, sales_id = ?, total_purchase = ?, 
              points_amount = ? WHERE points_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iidii", $membership_id, $sales_id, $total_purchase, $points_amount, $points_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Points updated successfully']);
    } else {
        throw new Exception('Failed to update points');
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>