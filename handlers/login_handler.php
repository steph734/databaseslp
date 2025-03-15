<?php
include '../database/database.php';
session_start();

function logAudit($conn, $admin_id, $action, $description)
{
    $stmt = $conn->prepare("INSERT INTO AuditLog (admin_id, action, description, timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $admin_id, $action, $description);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT admin_id, first_name, last_name, username, password, role, status FROM Admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($admin_id, $first_name, $last_name, $username, $hashed_password, $role, $status);
        $stmt->fetch();

        // Check if the account is active
        if ($status !== 'active') {
            $_SESSION['error'] = "deactivated";

            // Log deactivated account login attempt
            logAudit($conn, $admin_id, "Failed Login", "Deactivated account login attempt");

            header("Location: ../resource/login.php?error=deactivated");
            exit();
        }

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['login'] = "valid";

            // Log successful login
            logAudit($conn, $admin_id, "Login", "Successful admin login");

            header("Location: ../resource/layout/web-layout.php");
            exit();
        } else {
            $_SESSION['error'] = "invalid";

            // Log failed login attempt
            logAudit($conn, $admin_id, "Failed Login", "Incorrect password attempt");

            header("Location: ../resource/login.php?error=invalid");
            exit();
        }
    } else {
        $_SESSION['error'] = "notfound";

        // Log unknown user login attempt
        logAudit($conn, 0, "Failed Login", "Unknown username attempted login");

        header("Location: ../resource/login.php?error=notfound");
        exit();
    }
} else {
    $_SESSION['error'] = "method";
    echo "Invalid request method.";
}

$stmt->close();
$conn->close();