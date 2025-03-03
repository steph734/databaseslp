<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Returns</title>
    <link rel="stylesheet" href="css/dashboard.css"> 
    <link rel="stylesheet" href="css/returns.css"> 
    <link rel="stylesheet" href="css/customer.css"> 
    <link rel="stylesheet" href="css/sales.css"> 

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <ul>
            <li><a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a></li>
            <li><a href="customer.php"><i class="fa fa-user"></i> Customers</a></li>
            <li><a href="sales.php"><i class="fa fa-shopping-cart"></i> Sales</a></li>
            <li><a href="returns.php" class="active"><i class="fa fa-undo"></i> Returns</a></li>
            <li><a href="products.php"><i class="fa fa-box"></i> Products</a></li>
            <li><a href="inventory.php"><i class="fa fa-warehouse"></i> Inventory</a></li>
            <li><a href="supplier.php"><i class="fa fa-truck"></i> Suppliers</a></li>
            <li><a href="payments.php"><i class="fa fa-credit-card"></i> Payments</a></li>
            <li><a href="reports.php"><i class="fa fa-chart-bar"></i> Reports</a></li>
        </ul>
        <ul class="logout">
            <li><a href="#"><i class="fa fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <header>
            <h1>Returns</h1>
            <div class="search-profile">
                <input type="text" placeholder="Search...">
                <img src="profile.jpg" alt="Profile">
            </div>
        </header>

        <div class="search-container">
            <input type="text" placeholder="Customer ID">
            <input type="text" placeholder="Sales ID">
            <input type="text" placeholder="Product ID">
            <input type="text" placeholder="Reason">
            <button class="search-btn">SEARCH</button>
            <button class="clear-btn">CLEAR</button>
        </div>

        <div class="returns-table">
            <div class="table-controls">
                <button class="create-btn">CREATE NEW <span>+</span></button>
                <button class="edit-btn">EDIT <span>✏️</span></button>
                <button class="delete-btn">DELETE <span>🗑️</span></button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ReturnID</th>
                        <th>SalesID</th>
                        <th>ProductID</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="returns-table-body">
                    <!-- Data will be populated dynamically from the database -->
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>