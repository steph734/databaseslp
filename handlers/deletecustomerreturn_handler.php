<?php
session_start(); // Start session for success/error messages
include '../../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $return_id = $_POST['return_id'];

    // Use prepared statement to prevent SQL injection
    $query = "DELETE FROM customerreturn WHERE customer_return_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $return_id);

    if ($stmt->execute()) { 
        $_SESSION['success'] = "Customer return deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting customer return: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: ../returns.php"); // Redirect to returns page
    exit();
}
?>
