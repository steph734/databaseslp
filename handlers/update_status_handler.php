<?php
include '../database/database.php';
session_start();

header('Content-Type: application/json'); // Set response type to JSON

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receiving_id']) && isset($_POST['status'])) {
    $receiving_id = intval($_POST['receiving_id']);
    $status = strtolower($_POST['status']);

    $conn->begin_transaction();
    try {
        // Get current status to determine the transition
        $current_status_query = "SELECT status FROM Receiving WHERE receiving_id = ?";
        $current_stmt = $conn->prepare($current_status_query);
        $current_stmt->bind_param("i", $receiving_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        $current_row = $current_result->fetch_assoc();
        $current_status = strtolower($current_row['status']);
        $current_stmt->close();

        // Update the receiving status
        $stmt = $conn->prepare("UPDATE Receiving SET status = ? WHERE receiving_id = ?");
        $stmt->bind_param("si", $status, $receiving_id);
        $stmt->execute();
        $stmt->close();

        // Response array
        $response = ['status' => 'success', 'message' => 'Status updated successfully'];

        // Handle status-specific logic
        if ($status === "received" && $current_status !== "received") {
            // Update Product when changing TO "received"
            $supplier_query = "SELECT supplier_id FROM Receiving WHERE receiving_id = ?";
            $supplier_stmt = $conn->prepare($supplier_query);
            $supplier_stmt->bind_param("i", $receiving_id);
            $supplier_stmt->execute();
            $supplier_result = $supplier_stmt->get_result();
            $supplier_row = $supplier_result->fetch_assoc();
            $supplier_id = $supplier_row['supplier_id'];
            $supplier_stmt->close();

            $details_query = "SELECT product_id, quantity_furnished FROM Receiving_Details WHERE receiving_id = ?";
            $details_stmt = $conn->prepare($details_query);
            $details_stmt->bind_param("i", $receiving_id);
            $details_stmt->execute();
            $result = $details_stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $product_id = $row['product_id'];
                $quantity_furnished = $row['quantity_furnished'];

                $update_stmt = $conn->prepare("UPDATE Product SET quantity = quantity + ?, supplier_id = ? WHERE product_id = ?");
                $update_stmt->bind_param("iii", $quantity_furnished, $supplier_id, $product_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
            $details_stmt->close();

            $response['message'] = "Order marked as received. Product quantities and supplier updated.";
        } elseif ($status === "cancelled" && $current_status === "received") {
            // Message for cancellation after received
            $response['message'] = "Order cancelled. Products were already received and added to inventory. If you need to return the products, please use the return transaction process.";
            error_log("Receiving ID $receiving_id cancelled after being received. No automatic Product reversion applied.");
        } elseif ($status === "cancelled") {
            $response['message'] = "Order cancelled successfully.";
        }

        $conn->commit();
        echo json_encode($response);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

$conn->close();
