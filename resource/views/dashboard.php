<?php
include '../../database/database.php';

$username = ucfirst($_SESSION['username']);
$login = isset($_SESSION['login']) ? $_SESSION['login'] : '';
unset($_SESSION['login']);

// Total Sales
function getSalesData($conn)
{
    $today = $conn->query("SELECT SUM(total_amount) as total FROM sales WHERE sale_date = CURDATE()")->fetch_assoc()['total'] ?? 0;
    $month = $conn->query("SELECT SUM(total_amount) as total FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE())")->fetch_assoc()['total'] ?? 0;
    $year = $conn->query("SELECT SUM(total_amount) as total FROM sales WHERE YEAR(sale_date) = YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
    return [
        'today' => $today,
        'month' => $month,
        'year' => $year
    ];
}

// Total Revenue
function getTotalRevenue($conn)
{
    return $conn->query("SELECT SUM(total_amount) as total FROM sales")->fetch_assoc()['total'] ?? 0;
}

// Total Products in Stock
function getStockCount($conn)
{
    return $conn->query("SELECT SUM(stock_quantity) as total FROM inventory WHERE stock_quantity > 0")->fetch_assoc()['total'] ?? 0;
}

// Out of Stock Products
function getOutOfStock($conn)
{
    return $conn->query("SELECT COUNT(*) as total FROM inventory WHERE stock_quantity = 0")->fetch_assoc()['total'] ?? 0;
}

// Total Customers & Members
function getCustomerStats($conn)
{
    return $conn->query("SELECT COUNT(*) as total FROM customer WHERE is_member = 1")->fetch_assoc()['total'] ?? 0;
}

// Pending Orders
function getPendingOrders($conn)
{
    return $conn->query("SELECT COUNT(*) as total FROM receiving WHERE status = 'Pending'")->fetch_assoc()['total'] ?? 0;
}

// Total Suppliers
function getSupplierCount($conn)
{
    return $conn->query("SELECT COUNT(*) as total FROM supplier WHERE status = 'active'")->fetch_assoc()['total'] ?? 0;
}

// Returns & Refunds
function getReturnsStats($conn)
{
    $returns = $conn->query("SELECT COUNT(*) as total FROM customerreturn")->fetch_assoc()['total'] ?? 0;
    $refunds = $conn->query("SELECT COUNT(*) as total FROM customerreturn WHERE refund_status = 'Refunded'")->fetch_assoc()['total'] ?? 0;
    return ['returns' => $returns, 'refunds' => $refunds];
}

// Chart Data
function getMonthlySales($conn)
{
    $result = $conn->query("SELECT MONTH(sale_date) as month, SUM(total_amount) as total 
                           FROM sales 
                           WHERE YEAR(sale_date) = YEAR(CURDATE())
                           GROUP BY MONTH(sale_date)");
    $data = array_fill(1, 12, 0);
    while ($row = $result->fetch_assoc()) {
        $data[$row['month']] = (float)$row['total'];
    }
    return array_values($data);
}

function getStockLevels($conn)
{
    $result = $conn->query("SELECT c.category_name, SUM(i.stock_quantity) as qty 
                           FROM inventory i 
                           JOIN product p ON i.product_id = p.product_id 
                           JOIN category c ON p.category_id = c.category_id 
                           GROUP BY c.category_id 
                           LIMIT 5");
    $labels = [];
    $quantities = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['category_name'];
        $quantities[] = (int)$row['qty'];
    }
    return ['labels' => $labels, 'data' => $quantities];
}

function getTopProducts($conn)
{
    $result = $conn->query("SELECT p.product_name, SUM(sl.quantity) as qty 
                           FROM salesline sl 
                           JOIN product p ON sl.product_id = p.product_id 
                           GROUP BY sl.product_id 
                           ORDER BY qty DESC 
                           LIMIT 5");
    $labels = [];
    $quantities = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['product_name'];
        $quantities[] = (int)$row['qty'];
    }
    return ['labels' => $labels, 'data' => $quantities];
}

// Fetch all data
$sales = getSalesData($conn);
$revenue = getTotalRevenue($conn);
$stock = getStockCount($conn);
$outOfStock = getOutOfStock($conn);
$members = getCustomerStats($conn);
$pending = getPendingOrders($conn);
$suppliers = getSupplierCount($conn);
$returns = getReturnsStats($conn);
$monthlySales = getMonthlySales($conn);
$stockLevels = getStockLevels($conn);
$topProducts = getTopProducts($conn);
?>

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
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>
    <hr>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <a href="../layout/web-layout.php?page=sales" class="text-decoration-none">
                    <div class="card text-white bg-primary mb-3 clickable-card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-chart-line"></i> Total Sales</h5>
                            <p class="card-text">Today: ₱<?php echo number_format($sales['today'], 2); ?> |
                                This Month: ₱<?php echo number_format($sales['month'], 2); ?> |
                                This Year: ₱<?php echo number_format($sales['year'], 2); ?></p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="../layout/web-layout.php?page=sales" class="text-decoration-none">
                    <div class="card text-white bg-success mb-3 clickable-card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-peso-sign"></i> Total Revenue</h5>
                            <p class="card-text">₱<?php echo number_format($revenue, 2); ?></p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="../layout/web-layout.php?page=inventory" class="text-decoration-none">
                    <div class="card text-white bg-info mb-3 clickable-card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-box"></i> Products in Stock</h5>
                            <p class="card-text"><?php echo number_format($stock); ?> Items</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="../layout/web-layout.php?page=inventory" class="text-decoration-none">
                    <div class="card text-white bg-danger mb-3 clickable-card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-times-circle"></i> Out-of-Stock</h5>
                            <p class="card-text"><?php echo $outOfStock; ?> Items</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="../layout/web-layout.php?page=membership" class="text-decoration-none">
                    <div class="card text-white bg-secondary mb-3 clickable-card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-users"></i> Customers & Members</h5>
                            <p class="card-text"><?php echo number_format($members); ?> Members</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="../layout/web-layout.php?page=supplier" class="text-decoration-none">
                    <div class="card text-white bg-warning mb-3 clickable-card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-truck"></i> Pending Orders</h5>
                            <p class="card-text"><?php echo $pending; ?> Pending Deliveries</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="../layout/web-layout.php?page=supplier" class="text-decoration-none">
                    <div class="card text-white bg-dark mb-3 clickable-card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-building"></i> Suppliers</h5>
                            <p class="card-text"><?php echo $suppliers; ?> Suppliers</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-4">
                <a href="../layout/web-layout.php?page=returns" class="text-decoration-none">
                    <div class="card bg-light text-dark mb-3 clickable-card">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa fa-undo"></i> Returns & Refunds</h5>
                            <p class="card-text"><?php echo $returns['returns']; ?> Returns |
                                <?php echo $returns['refunds']; ?> Refunds</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <hr>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sales Trend</h5>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

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

<!-- Make sure to include Chart.js before your custom script -->
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
<script src="../js/dashboard.js"></script>
<script>
setTimeout(function() {
    let alert = document.querySelector(".floating-alert");
    if (alert) {
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    }
}, 4000);

// Pass PHP data to JavaScript
const monthlySales = <?php echo json_encode($monthlySales); ?>;
const stockLabels = <?php echo json_encode($stockLevels['labels']); ?>;
const stockData = <?php echo json_encode($stockLevels['data']); ?>;
const productLabels = <?php echo json_encode($topProducts['labels']); ?>;
const productData = <?php echo json_encode($topProducts['data']); ?>;
</script>

<style>
.clickable-card {
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.clickable-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
</style>