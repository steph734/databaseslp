<?php
include '../database/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receiving_id']) && isset($_POST['status'])) {
    $receiving_id = intval($_POST['receiving_id']);
    $status = $_POST['status'];

    $conn->begin_transaction();
    try {
        // Update the receiving status
        $stmt = $conn->prepare("UPDATE Receiving SET status = ? WHERE receiving_id = ?");
        $stmt->bind_param("si", $status, $receiving_id);
        $stmt->execute();
        $stmt->close();

        // If status is "received," update product quantities and supplier_id
        if (strtolower($status) === "received") {
            // Get supplier_id from Receiving
            $supplier_query = "SELECT supplier_id FROM Receiving WHERE receiving_id = ?";
            $supplier_stmt = $conn->prepare($supplier_query);
            $supplier_stmt->bind_param("i", $receiving_id);
            $supplier_stmt->execute();
            $supplier_result = $supplier_stmt->get_result();
            $supplier_row = $supplier_result->fetch_assoc();
            $supplier_id = $supplier_row['supplier_id'];
            $supplier_stmt->close();

            // Update products
            $details_query = "SELECT product_id, quantity_furnished FROM Receiving_Details WHERE receiving_id = ?";
            $details_stmt = $conn->prepare($details_query);
            $details_stmt->bind_param("i", $receiving_id);
            $details_stmt->execute();
            $result = $details_stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $product_id = $row['product_id'];
                $quantity_furnished = $row['quantity_furnished'];

                // Update quantity and supplier_id in Product table
                $update_stmt = $conn->prepare("UPDATE Product SET quantity = quantity + ?, supplier_id = ? WHERE product_id = ?");
                $update_stmt->bind_param("iii", $quantity_furnished, $supplier_id, $product_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
            $details_stmt->close();
        }

        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request";
}

$conn->close();