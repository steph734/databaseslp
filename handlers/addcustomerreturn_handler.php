<?php
include '../../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $return_id = $_POST['customer_return_id'];
    $customer_id = $_POST['customer_id'];
    $return_reason = $_POST['return_reason'];
    $return_date = $_POST['return_date'];
    $refund_status = $_POST['refund_status'];
    $total_amount = $_POST['total_amount'];
    $updatedbyid = $_POST['updatedbyid'] ?? null;
    $updatedate = date('Y-m-d H:i:s'); // Automatically set the updated timestamp

    // Use a prepared statement for security
    $query = "UPDATE customerreturn SET 
                customer_id = ?, 
                return_reason = ?, 
                return_date = ?, 
                refund_status = ?, 
                total_amount = ?, 
                updatedbyid = ?, 
                updatedate = ? 
              WHERE customer_return_id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isssdisi", 
        $customer_id, $return_reason, $return_date, 
        $refund_status, $total_amount, $updatedbyid, 
        $updatedate, $return_id
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../returns.php?success=Customer return updated successfully");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
