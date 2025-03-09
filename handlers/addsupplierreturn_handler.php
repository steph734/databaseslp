<?php
include '../../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_id = $_POST['supplier_id'];
    $product_id = $_POST['product_id'];
    $reason = $_POST['reason'];
    $date = $_POST['date'];
    $status = $_POST['status'];

    $query = "INSERT INTO supplierreturn (supplier_id, return_reason, return_date, refund_status) 
              VALUES ('$supplier_id', '$reason', '$date', '$status')";

    if (mysqli_query($conn, $query)) {
        header("Location: ../returns.php?success=Supplier return added");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
