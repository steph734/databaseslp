<?php
include 'database/db.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer</title>
    <link rel="stylesheet" href="css/dashboard.css"> 
    <link rel="stylesheet" href="css/customer.css"> 
    <link rel="stylesheet" href="css/sales.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

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
            <li><a href="#"><i class="fa fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </div>
    

    <div class="main-content">
        <header>
            <h1>Welcome, Kokey!</h1>
            <div class="search-profile">
                <input type="text" placeholder="Search...">
                <img src="profile.jpg" alt="Profile">
            </div>
        </header>

        <div class="search-container">

        <div class="search">
        <input type="text" placeholder="" style="width: 300px;">
            <button class="btn btn-success">SEARCH</button>
            <button class="btn btn-primary">CLEAR</button>

        </div>
        
        </div>

        <div class="customer-table">
            <div class="table-controls">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createmodal">CREATE NEW <span>+</span></button>
                <div class="modal" id="createmodal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Customer</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form>
                                   <div class="mb-3">
                                   <label class="form-label">Firstname</label>
                                    <input type="text"  class="form-control">
                                   </div>

                                   <div class="mb-3">
                                   <label class="form-label">Middlename</label>
                                    <input type="text"  class="form-control">
                                   </div>

                                   <div class="mb-3">
                                   <label class="form-label">Lastname</label>
                                    <input type="text"  class="form-control">
                                   </div>

                                   <div class="mb-3">
                                   <label class="form-label">Contact</label>
                                    <input type="text"  class="form-control">
                                   </div>

                                   <div class="mb-3">
                                   <label class="form-label">Address</label>
                                    <input type="text"  class="form-control">
                                   </div>
                                   <div class="mb-3">
                            <label class="form-label">Customer Type</label>
                         <div class="dropdown">
                     <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">Customer Type
                        </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="#">Regular Customer</a></li>
                     <li><a class="dropdown-item" href="#">Walk-in Customer</a></li>
                     <li><a class="dropdown-item" href="#">Member</a></li>
                </ul>
            </div>
            </div>
                                </form>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-success">Submit</button>
                                <button type="clear" class="btn btn-primary">Clear  </button>
                                <button type="cancel" class="btn btn-danger">Cancel</button>
                            </div>
                            </div>
                        </div>
                    </div>

                </div>
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editmodal">EDIT <span>✏️</span></button>
                   
                <button class="btn btn-danger">DELETE <span>🗑️</span></button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>CustomerID</th>
                        <th>TypeID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Member</th>
                    </tr>
                </thead>
                <tbody id="customer-table-body">
                    <!-- Data will be populated dynamically from the database -->
                </tbody>
            </table>
        </div>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</html>