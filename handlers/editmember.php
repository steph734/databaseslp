<?php
// editmember.php
error_reporting(E_ALL); // Enable error reporting for debugging
ini_set('display_errors', 1);

include '../database/database.php'; // Include database connection

// Verify database connection
if (!$conn) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]));
}

// Check if the request is POST and required fields are present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    // Sanitize and validate input
    $membership_id = filter_input(INPUT_POST, 'membership_id', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $date_renewal = filter_input(INPUT_POST, 'daterenewal', FILTER_SANITIZE_STRING);

    // Log received data for debugging
    error_log("Received data - membership_id: $membership_id, status: $status, date_renewal: $date_renewal");

    // Basic validation
    if (empty($membership_id) || empty($status)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Membership ID and Status are required',
            'data' => $_POST
        ]);
        exit();
session_start();
require_once '../database/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $membership_id = $_POST['membership_id'] ?? '';
    $customer_id = $_POST['customer_id'] ?? '';
    $status = $_POST['status'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $renewal_date = $_POST['renewal_date'] ?? '';
    $updatedbyid = $_SESSION['user_id'] ?? 1;

    if (empty($membership_id) || empty($customer_id) || empty($status)) {
        throw new Exception('Membership ID, Customer ID, and status are required');
    }

    $stmt = $conn->prepare("UPDATE membership SET customer_id = ?, status = ?, date_repairs = ?, 
                           date_renewal = ?, updatedbyid = ?, updatedate = NOW() 
                           WHERE membership_id = ?");
    $stmt->bind_param("isssii", $customer_id, $status, $start_date, $renewal_date, $updatedbyid, $membership_id);
    
    if ($stmt->execute()) {
        header("Location: ../resource/layout/web-layout.php?page=membership");
        exit();
    } else {
        throw new Exception('Failed to update member');
    }

    try {
        // If status is a foreign key to status table, it should be an integer
        $query = "UPDATE membership 
                  SET status = ?, 
                      date_renewal = ?, 
                      updatedbyid = ?, 
                      updatedate = NOW()
                  WHERE membership_id = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Replace with actual user ID from session if available
        $updated_by = 1; // Should come from $_SESSION['user_id'] or similar
        
        // If status is an integer in your DB, use "isis" instead of "ssis"
        // Adjust based on your actual database schema
        $stmt->bind_param(
            "ssis", // s = string, i = integer
            $status,
            $date_renewal,
            $updated_by,
            $membership_id
        );

        // Execute and check affected rows
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Membership updated successfully'
                ]);
            } else {
                throw new Exception("No rows updated - membership ID not found");
            }
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error updating membership: ' . $e->getMessage(),
            'sql_error' => $stmt->error ?? $conn->error
        ]);
    }
} else {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method',
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
}

$conn->close();
exit();
