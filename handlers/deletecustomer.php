<?php 

include '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    $customer_id = $_POST['customer_id'];
    $deletedbyid = $_SESSION['admin_id'] ?? NULL; // Default to 1 if session admin ID is not set

    $query = "DELETE FROM Customer WHERE customer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    
    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=customer");
        exit();
    } else {
        header("Location: ../resource/layout/web-layout.php?error=Failed to update customer");
        exit();
    }
}






?>