<?php
include '../database/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/suppliers.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_name = $_POST['supplier_name'] ?? '';
    $contact_info = $_POST['contact_info'] ?? '';
    $address = $_POST['address'] ?? '';
    $createdbyid = $_SESSION['admin_id'];

    if (empty($supplier_name)) {
        $_SESSION['error'] = "Supplier name is required.";
        header("Location: ../resource/views/supplier.php?error=missing_fields");
        exit();
    }

    $stmt = $conn->prepare("CALL AddSupplier(?, ?, ?, ?)");
    $stmt->bind_param("sssi", $supplier_name, $contact_info, $address, $createdbyid);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Supplier added successfully!";
        header("Location: ../resource/layout/web-layout.php?page=supplier");
    } else {
        $_SESSION['error'] = "Failed to add supplier.";
        header("Location: ../resource/layout/web-layout.php?page=supplier");
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=supplier");
}

<<<<<<< HEAD
$conn->close();
=======
$conn->close();
>>>>>>> 3e1a7cf36debfc72d0a4b43a979122b10df7cd12
