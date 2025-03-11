<?php
include '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['return_id']) && !empty($_POST['return_id'])) {
        $return_id = $_POST['return_id'];

        // Use Prepared Statements to Prevent SQL Injection
        $query = "DELETE FROM customerreturn WHERE customer_return_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $return_id); // "i" = integer

        if (mysqli_stmt_execute($stmt)) {
            header("Location: ../returns.php?success=Customer return deleted");
            exit(); // Ensure script stops execution after redirect
        } else {
            echo "Error: " . mysqli_error($conn);
        }

        // Close Statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Error: Return ID is missing!";
    }
} else {
    echo "Invalid request!";
}

// Close Database Connection
mysqli_close($conn);
?>