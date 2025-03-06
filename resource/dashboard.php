<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$username = ucfirst($username);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php" class="active"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="customer.php"><i class="fa fa-user"></i> Customers</a></li>
            <li><a href="sales.php"><i class="fa fa-shopping-cart"></i> Sales</a></li>
            <li><a href="returns.php"><i class="fa fa-undo"></i> Returns</a></li>
            <li><a href="products.php"><i class="fa fa-box"></i> Products</a></li>
            <li><a href="inventory.php"><i class="fa fa-warehouse"></i> Inventory</a></li>
            <li><a href="supplier.php"><i class="fa fa-truck"></i> Suppliers</a></li>
            <li><a href="payments.php"><i class="fa fa-credit-card"></i> Payments</a></li>
            <li><a href="reports.php"><i class="fa fa-chart-bar"></i> Reports</a></li>
        </ul>
        <ul class="logout">
            <li><a href="login.php"><i class="fa fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </div>


    <div class="main-content">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <div class="search-profile">
                <form action="">
                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" placeholder="Search...">
                        <!-- update -->
                    </div>
                </form>
                <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
            </div>
        </header>

        <div class="dashboard-content">
            <div class="chart">
                <h2>Sales Trends</h2>
                <img src="sales-chart.png" alt="Sales Trends">
            </div>
            <div class="chart">
                <h2>Stocks Level</h2>
                <img src="stocks-chart.png" alt="Stocks Level">
            </div>
        </div>
    </div>
</body>

</html>