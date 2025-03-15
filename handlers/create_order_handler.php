<?php
include '../database/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/layout/web-layout.php?page=supplier&error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $supplier_id = $_POST['supplier_id'] ?? '';
    $products = $_POST['products'] ?? [];

    if (empty($supplier_id) || empty($products)) {
        $_SESSION['error'] = "Supplier and products are required.";
        header("Location: ../resource/layout/web-layout.php?page=supplier&error=missing_data");
        exit();
    }

    $conn->begin_transaction();
    try {
        $total_quantity = 0;
        $total_cost = 0;

        foreach ($products as $product) {
            $total_quantity += $product['quantity'];
            $total_cost += $product['quantity'] * $product['unit_cost'];
        }

        // Insert into Receiving
        $receiving_query = "INSERT INTO Receiving (supplier_id, receiving_date, total_quantity, total_cost, status) 
                            VALUES (?, NOW(), ?, ?, 'Pending')";
        $stmt = $conn->prepare($receiving_query);
        $stmt->bind_param("iid", $supplier_id, $total_quantity, $total_cost);
        $stmt->execute();
        $receiving_id = $stmt->insert_id;
        $stmt->close();

        // Insert into Receiving_Details
        $receiving_details_query = "INSERT INTO Receiving_Details (receiving_id, product_id, quantity_furnished, unit_cost, subtotal_cost, `condition`, createdbyid, createdate) 
                                    VALUES (?, ?, ?, ?, ?, 'Good', ?, NOW())";
        $stmt = $conn->prepare($receiving_details_query);

        foreach ($products as $product) {
            $product_id = $product['product_id'];
            $quantity = $product['quantity'];
            $unit_cost = $product['unit_cost'];
            $subtotal = $quantity * $unit_cost;
            $created_by = $_SESSION['admin_id'];

            // Validate product_id exists
            $check_query = "SELECT product_id FROM Product WHERE product_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Product not found with ID: " . htmlspecialchars($product_id));
            }
            $check_stmt->close();

            $stmt->bind_param("iiiddi", $receiving_id, $product_id, $quantity, $unit_cost, $subtotal, $created_by);
            $stmt->execute();
        }
        $stmt->close();

        $conn->commit();
        $_SESSION['success'] = "Order created successfully!";
        header("Location: ../resource/layout/web-layout.php?page=supplier");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error creating order: " . $e->getMessage();
        header("Location: ../resource/layout/web-layout.php?page=supplier&error=database_error");
        exit();
    }
}

$_SESSION['error'] = "Invalid request.";
header("Location: ../resource/layout/web-layout.php?page=supplier&error=invalid_request");
exit();