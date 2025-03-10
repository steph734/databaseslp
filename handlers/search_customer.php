<?php
session_start();
include '../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_query = trim($_POST['search_query']);

    if (!empty($search_query)) {
        // Search by Customer ID, Name, Contact, or Address
        $query = "SELECT * FROM Customer 
                  WHERE customer_id LIKE ? 
                     OR name LIKE ? 
                     OR contact LIKE ? 
                     OR address LIKE ?";
        
        $stmt = $conn->prepare($query);
        $search_param = "%{$search_query}%";
        $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();

        $_SESSION['search_results'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Redirect back to customer.php to display results
header("Location: ../views/customer.php");
exit;
