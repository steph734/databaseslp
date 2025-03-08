<div class="main-content">
    <?php if ($login == "valid") : ?>
        <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert">
            Login successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>


    <header>
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>
    <hr>
    <div class="container mt-4">
        <div class="row">
            <!-- Total Sales -->
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa fa-chart-line"></i> Total Sales</h5>
                        <p class="card-text">Today: ₱1,500.00 | This Month: ₱25,000.00 | This Year: ₱300,000.00</p>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa fa-peso-sign"></i> Total Revenue</h5>
                        <p class="card-text">₱1,250,000.00</p>
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
    <div class="container mt-4">
        <hr>
        <div class="row">
            <!-- Sales Trend Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sales Trend</h5>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Stock Level Chart -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Stock Levels</h5>
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Top Selling Products -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top Selling Products</h5>
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/dashboard.js"></script>
<script>
    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>