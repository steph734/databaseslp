    <?php
    session_start();
    include '../database/database.php';

    if (!isset($_SESSION['admin_id'])) {
        $_SESSION['error'] = "Unauthorized access!";
        header("Location: ../resource/views/inventory.php?error=unauthorized");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['return_id']) && !empty($_POST['return_id'])) {
        $return_ids = $_POST['return_id']; // Fix: Use it directly since it's already an array

        try {
            $conn->begin_transaction();

            // Prepare the delete query
            $query = "DELETE FROM customerreturn WHERE customer_return_id IN (" . implode(',', array_fill(0, count($return_ids), '?')) . ")";
            $delete_stmt = $conn->prepare($query);

            if ($delete_stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            // Bind parameters dynamically
            $types = str_repeat('i', count($return_ids));
            $delete_stmt->bind_param($types, ...$return_ids);

            if ($delete_stmt->execute()) {
                if ($delete_stmt->affected_rows > 0) {
                    $conn->commit();
                    $_SESSION['success'] = "Customer returns deleted successfully!";
                } else {
                    $conn->rollback();
                    $_SESSION['error'] = "No customer return records found with the provided IDs!";
                }
            } else {
                $conn->rollback();
                $_SESSION['error'] = "Error processing deletion: " . $conn->error;
            }

            $delete_stmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error processing deletion: " . $e->getMessage();
            error_log("Exception: " . $e->getMessage());
        }
    } else {
        $_SESSION['error'] = "Invalid request!";
    }

    $conn->close();
    header("Location: ../resource/layout/web-layout.php?page=returns");
    exit();
    ?>
