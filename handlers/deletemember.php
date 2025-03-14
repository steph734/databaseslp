<?php
session_start();
require_once '../database/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
        throw new Exception('Invalid request');
    }

    $membership_id = $_GET['id'];
    $updatedbyid = $_SESSION['user_id'] ?? 1;

    $stmt = $conn->prepare("DELETE FROM membership WHERE membership_id = ?");
    $stmt->bind_param("i", $membership_id);
    
    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=membership");
        exit();
    } else {
        throw new Exception('Failed to delete member');
    }

} catch (Exception $e) {
    error_log("Error in deletemember.php: " . $e->getMessage());
    header("Location: ../../path/to/your/main_page.php?error=" . urlencode($e->getMessage()));
    exit();
} finally {
    $stmt->close();
    $conn->close();
}
?>