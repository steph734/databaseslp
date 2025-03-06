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
    <link rel="stylesheet" href="../statics/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>

<body>
    <div class="sidebar">
        <ul>
            <li style="background-color: whitesmoke; border-radius: 5px; color: #34502b !important;"><a
                    href="dashboard.php" class="active" style="color: #34502b;"><i class="fa fa-home"
                        style="margin-right: 10px; color: #34502b;"></i>
                    Dashboard</a></li>
            <li><a href="customer.php"><i class="fa fa-user" style="margin-right: 10px;"></i> Customers</a></li>
            <li><a href="sales.php"><i class="fa fa-shopping-cart" style="margin-right: 10px;"></i> Sales</a></li>
            <li><a href="returns.php"><i class="fa fa-undo" style="margin-right: 10px;"></i> Returns</a></li>
            <li><a href="products.php"><i class="fa fa-box" style="margin-right: 10px;"></i> Products</a></li>
            <li><a href="inventory.php"><i class="fa fa-warehouse" style="margin-right: 10px;"></i> Inventory</a></li>
            <li><a href="supplier.php"><i class="fa fa-truck" style="margin-right: 10px;"></i> Suppliers</a></li>
            <li><a href="payments.php"><i class="fa fa-credit-card" style="margin-right: 10px;"></i> Payments</a></li>
            <li><a href="reports.php"><i class="fa fa-chart-bar" style="margin-right: 10px;"></i> Reports</a></li>
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

        <!-- Dashboard Overview Panel -->
        <div class="container mt-4">
            <div class="row">
                <!-- Total Sales -->
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-chart-line"></i> Total Sales</h5>
                            <p class="card-text">Today: $1,500 | This Month: $25,000 | This Year: $300,000</p>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-dollar-sign"></i> Total Revenue</h5>
                            <p class="card-text">$1,250,000</p>
                        </div>
                    </div>
                </div>

                <!-- Total Products in Stock -->
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-box"></i> Products in Stock</h5>
                            <p class="card-text">1,500 Items</p>
                        </div>
                    </div>
                </div>

                <!-- Out-of-Stock Products -->
                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-times-circle"></i> Out-of-Stock</h5>
                            <p class="card-text">25 Items</p>
                        </div>
                    </div>
                </div>

                <!-- Total Customers & Members -->
                <div class="col-md-4">
                    <div class="card text-white bg-secondary mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-users"></i> Customers & Members</h5>
                            <p class="card-text">3,200 Members</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders -->
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-truck"></i> Pending Orders</h5>
                            <p class="card-text">12 Pending Deliveries</p>
                        </div>
                    </div>
                </div>

                <!-- Total Suppliers -->
                <div class="col-md-4">
                    <div class="card text-white bg-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-building"></i> Suppliers</h5>
                            <p class="card-text">18 Suppliers</p>
                        </div>
                    </div>
                </div>

                <!-- Returns & Refunds -->
                <div class="col-md-4">
                    <div class="card bg-light text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-undo"></i> Returns & Refunds</h5>
                            <p class="card-text">5 Returns | 2 Refunds</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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