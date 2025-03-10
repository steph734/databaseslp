<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../resource/login.php");
    exit();
}
$username = ucfirst($_SESSION['username']);

$login = isset($_SESSION['login']) ? $_SESSION['login'] : '';
unset($_SESSION['login']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- css -->
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/inventory.css">
    <link rel="stylesheet" href="../css/customer.css">
    <link rel="stylesheet" href="../css/sales.css">
    <link rel="stylesheet" href="../css/supplier.css">
    <link rel="stylesheet" href="../css/products.css">
    <link rel="stylesheet" href="../css/returns.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/membership.css">
    <!-- css -->
    <link rel="stylesheet" href="../../statics/css/bootstrap.min.css">
    <script src="../../statics/js/bootstrap.bundle.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
    html,
    body {
        overflow-x: hidden;
    }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../views/sidebar.php'; ?>

    <!-- main content -->
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    $allowed_pages = ['dashboard', 'customer', 'sales', 'products', 'inventory', 'supplier', 'returns', 'reports', 'membership'];
    $page_path = __DIR__ . "/../views/{$page}.php";

    if (in_array($page, $allowed_pages) && file_exists($page_path)) {
        include $page_path;
    } else {
        echo "<h2>Page not found</h2>";
    }
    ?>


</body>

</html>