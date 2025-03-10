<<<<<<< HEAD
=======

>>>>>>> 3e1a7cf36debfc72d0a4b43a979122b10df7cd12
<?php
session_start();
include '../database/database.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/products.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $unitofmeasurement = $_POST['unitofmeasurement'];
    $category_id = $_POST['category_id'];
    $supplier_id = $_POST['supplier_id'];
    $updatedbyid = $_SESSION['admin_id'];

    // Convert invalid supplier_id values to NULL
    $supplier_id = (!empty($supplier_id) && $supplier_id != "0" && strtoupper($supplier_id) != "N/A") ? $supplier_id : NULL;

    try {
        // Enable MySQLi Exception Mode
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $stmt = $conn->prepare("CALL UpdateProduct(?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isidssii", $product_id, $product_name, $quantity, $price, $unitofmeasurement, $category_id, $supplier_id, $updatedbyid);

        $stmt->execute();
        $_SESSION['success'] = "Product updated successfully!";
    } catch (mysqli_sql_exception $e) {
        // Handle foreign key constraint errors
        if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
            $_SESSION['error'] = "Supplier is not applicable or does not exist.";
        } else {
            $_SESSION['error'] = "Error updating product: " . $e->getMessage();
        }
    }


    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();

    // Redirect back to the products page
    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
}
