<?php
session_start();
include '../database/database.php';

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/views/products.php?error=unauthorized");
    exit();
}

if (isset($_GET['id'])) {
    $product_ids = [$_GET['id']];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $product_ids = $data['product_ids'] ?? [];
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
}

if (empty($product_ids)) {
    $_SESSION['error'] = "No product IDs provided.";
    header("Location: ../resource/layout/web-layout.php?page=products");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Check current statuses
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$stmt = $conn->prepare("SELECT product_id, status FROM Product WHERE product_id IN ($placeholders)");
$types = str_repeat('i', count($product_ids));
$stmt->bind_param($types, ...$product_ids);
$stmt->execute();
$result = $stmt->get_result();

$already_unavailable = [];
$to_update = [];
while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'unavailable') {
        $already_unavailable[] = $row['product_id'];
    } else {
        $to_update[] = $row['product_id'];
    }
}
$stmt->close();

if (empty($to_update)) {
    if (isset($_GET['id'])) {
        $_SESSION['error'] = "This product is already unavailable.";
        header("Location: ../resource/layout/web-layout.php?page=products");
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'All selected products are already unavailable.']);
    }
    exit();
}

$stmt = $conn->prepare("UPDATE Product SET status = 'unavailable', updatedbyid = ?, updatedate = NOW() WHERE product_id = ?");
$success = true;

foreach ($to_update as $product_id) {
    $stmt->bind_param("ii", $admin_id, $product_id);
    if (!$stmt->execute() || $stmt->affected_rows == 0) {
        $success = false;
    }
}

$stmt->close();

if (isset($_GET['id'])) {
    if ($success) {
        $_SESSION['success'] = "Product marked as unavailable successfully!";
    } else {
        $_SESSION['error'] = "Failed to mark product as unavailable.";
    }
    header("Location: ../resource/layout/web-layout.php?page=products");
} else {
    header('Content-Type: application/json');
    $message = $success ? "Products marked as unavailable successfully!" : "Failed to mark some products as unavailable.";
    if (!empty($already_unavailable)) {
        $message .= " Products " . implode(', ', $already_unavailable) . " were already unavailable.";
    }
    echo json_encode(['success' => $success, 'error' => $success ? null : $message, 'message' => $message]);
}
exit();
