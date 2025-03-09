<?php
include '../../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $return_id = $_POST['return_id'];

    $query = "DELETE FROM customerreturn WHERE customer_return_id = '$return_id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../returns.php?success=Customer return deleted");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
