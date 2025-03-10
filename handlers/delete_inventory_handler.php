<?php
<<<<<<< HEAD
include '../database/database.php';
session_start();

if (isset($_GET['id'])) {
    $inventory_id = $_GET['id'];

    $query = "DELETE FROM Inventory WHERE inventory_id='$inventory_id'";

    if ($conn->query($query)) {
        session_start();
        $_SESSION['success'] = "Inventory deleted successfully!";
    } else {
        session_start();
        $_SESSION['error'] = "Error: " . $conn->error;
    }

    header("Location: ../resource/layout/web-layout.php?page=inventory");
    exit();
}
?>
=======
session_start();
include '../database/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/inventory.php?error=unauthorized");
    exit();
}

// Check if request method is GET and 'id' is set
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $inventory_id = (int)$_GET['id']; // Ensure it's an integer to prevent SQL injection
    error_log("Received inventory_id: $inventory_id"); // Debug log
    $updatedbyid = $_SESSION['admin_id'] ?? 1; // Default to 1 if not set
    $updatedate = date('Y-m-d H:i:s');

    // Validate inventory_id
    if ($inventory_id <= 0) {
        $_SESSION['error'] = "Invalid inventory ID!";
        header("Location: ../resource/layout/web-layout.php?page=inventory&error=invalid");
        exit();
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Fetch the inventory record to check stock details
        $select_stmt = $conn->prepare("SELECT product_id, stock_quantity, damage_stock FROM Inventory WHERE inventory_id = ?");
        if ($select_stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $select_stmt->bind_param("i", $inventory_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $inventory = $result->fetch_assoc();
        error_log("Query for inventory_id $inventory_id returned: " . print_r($inventory, true)); // Debug log
        $select_stmt->close();

        if ($inventory) {
            $product_id = $inventory['product_id'];
            $stock_quantity = $inventory['stock_quantity'];
            $damage_stock = $inventory['damage_stock'];

            // Delete the inventory record
            $delete_stmt = $conn->prepare("DELETE FROM Inventory WHERE inventory_id = ?");
            $delete_stmt->bind_param("i", $inventory_id);

            // Update Product quantity to 0 (pull out the product)
            $update_product_stmt = $conn->prepare("UPDATE Product SET quantity = 0, updatedbyid = ?, updatedate = ? WHERE product_id = ?");
            $update_product_stmt->bind_param("iss", $updatedbyid, $updatedate, $product_id);

            if ($delete_stmt->execute() && $update_product_stmt->execute()) {
                if ($delete_stmt->affected_rows > 0) {
                    $conn->commit();
                    $_SESSION['success'] = "Inventory record ID $inventory_id and product ID $product_id have been pulled out successfully!";
                } else {
                    $conn->rollback();
                    $_SESSION['error'] = "No inventory record found with ID $inventory_id!";
                }
                $delete_stmt->close();
                $update_product_stmt->close();
            } else {
                $conn->rollback();
                $_SESSION['error'] = "Error processing deletion or product update: " . $conn->error . " (Check for foreign key constraints)";
            }
        } else {
            $conn->rollback();
            $_SESSION['error'] = "Inventory record with ID $inventory_id not found! Debug: Result was null.";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error processing deletion: " . $e->getMessage();
        error_log("Exception: " . $e->getMessage());
    }
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../resource/layout/web-layout.php?page=inventory");
}

$conn->close();
header("Location: ../resource/layout/web-layout.php?page=inventory");
exit();
>>>>>>> 2fbec378724fa20bea82d684e948bc4edecb67a8
