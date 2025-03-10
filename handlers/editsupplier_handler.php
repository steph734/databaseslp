
<?php
session_start();
include '../database/database.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../views/suppliers.php?error=unauthorized");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $supplier_id = $_POST['supplier_id'];
    $supplier_name = $_POST['supplier_name'];
    $contact_info = $_POST['contact_info'];
    $address = $_POST['address'];
    $updatedbyid = $_SESSION['admin_id']; // Get admin ID from session

    try {
        // Enable MySQLi Exception Mode
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Call the stored procedure
        $stmt = $conn->prepare("CALL UpdateSupplier(?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $supplier_id, $supplier_name, $contact_info, $address, $updatedbyid);

        $stmt->execute();
        $_SESSION['success'] = "Supplier updated successfully!";
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error'] = "Error updating supplier: " . $e->getMessage();
    }

    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();

    // Redirect back to the suppliers page
    header("Location: ../resource/layout/web-layout.php?page=supplier");
    exit();
}
