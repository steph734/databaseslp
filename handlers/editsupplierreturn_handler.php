<?php
include '../../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $return_id = $_POST['return_id'];
    $supplier_id = $_POST['supplier_id'];
    $product_id = $_POST['product_id'];
    $reason = $_POST['reason'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    $query = "UPDATE supplierreturn SET 
                supplier_id = '$supplier_id', 
                return_reason = '$reason', 
                return_date = '$date', 
                refund_status = '$status' 
              WHERE supplier_return_id = '$return_id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../returns.php?success=Supplier return updated");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
