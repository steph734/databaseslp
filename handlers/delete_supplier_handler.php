<?php
session_start();
include '../database/database.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../views/suppliers.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $supplier_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM Supplier WHERE supplier_id = ?");
    $stmt->bind_param("i", $supplier_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Supplier deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting supplier.";
    }

    $stmt->close();
    $conn->close();

    header("Location: ../resource/layout/web-layout.php?page=supplier");
    exit();
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: ../resource/layout/web-layout.php?page=supplier");
    exit();
<<<<<<< HEAD
}
=======
}
>>>>>>> 3e1a7cf36debfc72d0a4b43a979122b10df7cd12
