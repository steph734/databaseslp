<?php
session_start();
include '../database/database.php';

// Check if the user is an admin and prevent unauthorized access
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/inventory.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inventory_id = $_POST['inventory_id'];
    $product_id = $_POST['product_id'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $total_value = $_POST['total_value'];
    $received_date = !empty($_POST['received_date']) ? $_POST['received_date'] : NULL;
    $last_restock_date = !empty($_POST['last_restock_date']) ? $_POST['last_restock_date'] : NULL;
    $damage_stock = $_POST['damage_stock'];
    $updated_by = $_SESSION['admin_id'];
    $updated_date = date("Y-m-d H:i:s");

    try {
        // Enable MySQLi Exception Mode for better error handling
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Prepare the stored procedure call
        $stmt = $conn->prepare("CALL EditInventory(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @p_message, @p_stock_level)");
        $stmt->bind_param(
            "iididssiss",
            $inventory_id,
            $product_id,
            $price,
            $stock_quantity,
            $total_value,
            $received_date,
            $last_restock_date,
            $damage_stock,
            $updated_by,
            $updated_date
        );

        $stmt->execute();

        // Retrieve the output messages from the stored procedure
        $result = $conn->query("SELECT @p_message AS message, @p_stock_level AS stock_level");
        $row = $result->fetch_assoc();

        // Check if the message indicates an error
        if (strpos($row['message'], 'Error:') === 0) {
            $_SESSION['error'] = $row['message'];
        } else {
            $_SESSION['success'] = $row['message'];
        }
    } catch (mysqli_sql_exception $e) {
        // Handle possible errors, including foreign key constraint issues
        if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
            $_SESSION['error'] = "Error: The selected product does not exist or is invalid.";
        } else {
            $_SESSION['error'] = "Database Error: " . $e->getMessage();
        }
    }

    // Close the statement and connection
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();

    // Redirect back to the inventory page
    header("Location: ../resource/layout/web-layout.php?page=inventory");
    exit();
}
