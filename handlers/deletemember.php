<?php
include '../database/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['membership_id'])) {
    $membership_id = $_POST['membership_id'];

    // Prepare statement to prevent SQL injection
    $query = "DELETE FROM membership WHERE membership_id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $membership_id);
        if ($stmt->execute()) {
            echo "Membership deleted successfully.";
        } else {
            echo "Error: Could not delete membership.";
        }
        $stmt->close();
    } else {
        echo "Error: Could not prepare statement.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
