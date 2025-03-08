<?php
include '../database/database.php';
session_start();

if (isset($_GET['id'])) {
    $inventory_id = $_GET['id'];

    $query = "DELETE FROM Inventory WHERE inventory_id='$inventory_id'";

    if ($conn->query($query)) {
        session_start();
        $_SESSION['success'] = "Inventory deleted successfully!";
    } else {
        session_start();
        $_SESSION['error'] = "Error: " . $conn->error;
    }

    header("Location: ../resource/layout/web-layout.php?page=inventory");
    exit();
}
?>