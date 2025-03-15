<?php
session_start();
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $address = $_POST['address'] ?? '';
    $type_id = $_POST['customertype'] ?? '';
    $membership_ids = $_POST['membership_ids'] ?? '';
    $updatedbyid = $_SESSION['admin_id'] ?? 1; // Assuming user_id is stored in session
    $updatedate = date('Y-m-d H:i:s');

    // Define REGULAR_TYPE_ID
    $REGULAR_TYPE_ID = '2';

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update customer details
        $update_customer_sql = "UPDATE Customer 
                                SET name = ?, contact = ?, address = ?, type_id = ?, 
                                    updatedbyid = ?, updatedate = ?
                                WHERE customer_id = ?";
        $stmt = $conn->prepare($update_customer_sql);
        $stmt->bind_param("sssisis", $name, $contact, $address, $type_id, $updatedbyid, $updatedate, $customer_id);
        $stmt->execute();

        // Handle membership logic
        if ($type_id == $REGULAR_TYPE_ID) {
            // If type is Regular, delete all memberships
            $delete_membership_sql = "DELETE FROM membership WHERE customer_id = ?";
            $stmt = $conn->prepare($delete_membership_sql);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
        } else if (!empty($membership_ids)) {
            // If type is not Regular and membership_ids are provided, update memberships
            $membership_array = array_filter(explode(',', $membership_ids));
            
            // Delete existing memberships
            $delete_membership_sql = "DELETE FROM membership WHERE customer_id = ?";
            $stmt = $conn->prepare($delete_membership_sql);
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            
            // Insert new memberships
            $insert_membership_sql = "INSERT INTO membership (customer_id, membership_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_membership_sql);
            foreach ($membership_array as $membership_id) {
                $membership_id = trim($membership_id);
                $stmt->bind_param("is", $customer_id, $membership_id);
                $stmt->execute();
            }
        }
        // If membership_ids is empty and type is not Regular, do nothing (leave existing memberships intact)

        // Commit transaction
        $conn->commit();

        // Redirect back to customer list
        header("Location: ../resource/layout/web-layout.php?page=customer");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}
?>