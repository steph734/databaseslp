<?php
session_start();
include '../../database/database.php';

if (isset($_GET['id'])) {
    $sales_id = (int)$_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM Sales WHERE sales_id = ?");
        $stmt->bind_param("i", $sales_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Sale deleted successfully.";
        } else {
            $_SESSION['error'] = "Sale not found.";
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting sale: " . $e->getMessage();
    }

    header("Location: ../resource/layout/web-layout.php?page=sales");
    exit();
}
