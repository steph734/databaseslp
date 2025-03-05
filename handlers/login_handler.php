<?php
include '../database/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT admin_id, first_name, last_name, username, password, role FROM Admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($admin_id, $first_name, $last_name, $username, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $admin_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Debugging: Check session data
            echo "<pre>";
            var_dump($_SESSION);
            echo "</pre>";
            header("Location: ../resource/dashboard.php");
            exit();
        } else {
            echo "Invalid password.";
            header("Location: ../resource/login.php?error=invalid"); // More specific error
            exit();
        }
    } else {
        echo "User not found.";
        header("Location: ../resource/login.php?error=notfound");
        exit();
    }
} else {
    echo "Invalid request method.";
}

$stmt->close();
$conn->close();