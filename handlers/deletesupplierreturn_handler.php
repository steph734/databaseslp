
<?php
include '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $return_id = $_POST['return_id'];

    $query = "DELETE FROM supplierreturn WHERE supplier_return_id = '$return_id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../returns.php?success=Supplier return deleted");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
