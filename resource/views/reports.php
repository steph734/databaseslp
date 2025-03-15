<?php
include '../../database/database.php';

// Check if admin is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/login.php");
    exit();
}

// Initialize variables
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'daily';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Prepare query based on report type
$sales_data = [];
$total_sales = 0;
$total_amount = 0;

// Function to fetch sales line items
function fetchSalesLineItems($conn, $sales_id)
{
    $line_query = "
        SELECT sl.quantity, sl.unit_price, sl.subtotal_price, p.product_name
        FROM SalesLine sl
        JOIN Product p ON sl.product_id = p.product_id
        WHERE sl.sales_id = ?
    ";
    $line_stmt = $conn->prepare($line_query);
    $line_stmt->bind_param("i", $sales_id);
    $line_stmt->execute();
    $line_result = $line_stmt->get_result();
    $items = [];
    while ($line_row = $line_result->fetch_assoc()) {
        $items[] = $line_row;
    }
    $line_stmt->close();
    return $items;
}

// Handler for generating reports
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['report_type'])) {
        if ($report_type === 'daily') {
            if (!DateTime::createFromFormat('Y-m-d', $date)) {
                throw new Exception("Invalid date format!");
            }

            $query = "
                SELECT s.sales_id, s.sale_date, s.total_amount, s.payment_method,
                       c.name as customer_name, 
                       a1.first_name as created_by_first, a1.last_name as created_by_last,
                       a2.first_name as updated_by_first, a2.last_name as updated_by_last
                FROM Sales s
                LEFT JOIN Customer c ON s.customer_id = c.customer_id
                LEFT JOIN Admin a1 ON s.createdbyid = a1.admin_id
                LEFT JOIN Admin a2 ON s.updatedbyid = a2.admin_id
                WHERE DATE(s.sale_date) = ?
                ORDER BY s.sale_date DESC
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $row['items'] = fetchSalesLineItems($conn, $row['sales_id']);
                $sales_data[] = $row;
                $total_amount += $row['total_amount'];
                $total_sales++;
            }
            $stmt->close();
        } elseif ($report_type === 'monthly') {
            if (!is_numeric($year) || $year < 2000 || $year > date('Y')) {
                throw new Exception("Invalid year!");
            }

            $query = "
                SELECT DATE_FORMAT(s.sale_date, '%Y-%m') as period, 
                       COUNT(*) as sales_count, 
                       SUM(s.total_amount) as total_amount
                FROM Sales s
                WHERE YEAR(s.sale_date) = ?
                GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m')
                ORDER BY period DESC
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $sales_data[] = $row;
                $total_sales += $row['sales_count'];
                $total_amount += $row['total_amount'];
            }
            $stmt->close();
        } elseif ($report_type === 'yearly') {
            $query = "
                SELECT YEAR(s.sale_date) as period, 
                       COUNT(*) as sales_count, 
                       SUM(s.total_amount) as total_amount
                FROM Sales s
                GROUP BY YEAR(s.sale_date)
                ORDER BY period DESC
            ";
            $result = $conn->query($query);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $sales_data[] = $row;
                    $total_sales += $row['sales_count'];
                    $total_amount += $row['total_amount'];
                }
            } else {
                throw new Exception("Error fetching yearly report: " . $conn->error);
            }
        } else {
            throw new Exception("Invalid report type!");
        }
        // Store report data in session for full report page
        $_SESSION['report_data'] = [
            'report_type' => $report_type,
            'sales_data' => $sales_data,
            'total_sales' => $total_sales,
            'total_amount' => $total_amount,
            'date' => $date,
            'year' => $year
        ];
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    $sales_data = [];
}
?>

<style>
html,
body {
    overflow-x: hidden;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

.main-content {
    margin-left: 250px;
    width: calc(100% - 250px);
    padding: 20px;
    overflow: hidden;
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: black;
    padding: 15px;
    border-radius: 5px;
}

.search-profile {
    display: flex;
    align-items: center;
}

.report-table {
    background: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
}

.btn-filter,
.btn-view,
.btn-print {
    background: #34502b;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.btn-filter:hover,
.btn-view:hover,
.btn-print:hover {
    background: white;
    color: #34502b;
    border: 1px solid #34502b;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    text-align: center;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

th {
    color: rgb(22, 21, 21) !important;
    background-color: #e6c200;
}

tr:hover {
    background: #f1f1f1;
}

.floating-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    transition: opacity 0.5s;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 200px;
        width: calc(100% - 200px);
    }
}

@media (max-width: 500px) {
    .main-content {
        margin-left: 0;
        width: 100%;
    }

    header {
        flex-direction: column;
        text-align: center;
    }
}
</style>
<!-- hello -->

<div class="main-content">
    <header>
        <h1>Sales Reports</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>
    <hr>

    <!-- Report Filter -->
    <div class="card p-4 mb-4">
        <h4>Filter Report</h4>
        <form method="GET" action="?page=reports" class="row g-3">
            <input type="hidden" name="page" value="reports">
            <div class="col-md-3">
                <label for="report_type" class="form-label">Report Type</label>
                <select class="form-control" id="report_type" name="report_type" onchange="toggleDateFields()">
                    <option value="daily" <?= $report_type === 'daily' ? 'selected' : '' ?>>Daily</option>
                    <option value="monthly" <?= $report_type === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    <option value="yearly" <?= $report_type === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                </select>
            </div>
            <div class="col-md-3" id="date_field" style="display: <?= $report_type === 'daily' ? 'block' : 'none' ?>;">
                <label for="date" class="form-label">Select Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?= htmlspecialchars($date) ?>">
            </div>
            <div class="col-md-3" id="year_field"
                style="display: <?= $report_type === 'monthly' ? 'block' : 'none' ?>;">
                <label for="year" class="form-label">Select Year</label>
                <select class="form-control" id="year" name="year">
                    <?php for ($y = date('Y'); $y >= 2020; $y--) : ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn-filter">Generate Report</button>
            </div>
        </form>
    </div>

    <!-- Report Summary -->
    <div class="card p-4 mb-4" id="summary-card">
        <h4>Summary</h4>
        <p>Total Sales: <?= $total_sales ?></p>
        <p>Total Amount: ₱<?= number_format($total_amount, 2) ?></p>
        <a href="../views/full_report.php" class="btn-view" style="width: 130px;">View Full
            Report</a>
    </div>

    <!-- Report Table -->
    <div class="report-table">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php if ($report_type === 'daily') : ?>
                        <th>Sales ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Total Amount (₱)</th>
                        <th>Payment Method</th>
                        <th>Created By</th>
                        <th>Updated By</th>
                        <th>Items</th>
                        <?php else : ?>
                        <th>Period</th>
                        <th>Number of Sales</th>
                        <th>Total Amount (₱)</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($sales_data) > 0) : ?>
                    <?php foreach ($sales_data as $sale) : ?>
                    <tr>
                        <?php if ($report_type === 'daily') : ?>
                        <td><?= htmlspecialchars($sale['sales_id']) ?></td>
                        <td><?= htmlspecialchars($sale['sale_date']) ?></td>
                        <td><?= htmlspecialchars($sale['customer_name'] ?? '-') ?></td>
                        <td><?= number_format($sale['total_amount'], 2) ?></td>
                        <td><?= htmlspecialchars($sale['payment_method']) ?></td>
                        <td><?= htmlspecialchars($sale['created_by_first'] . ' ' . $sale['created_by_last'] ?? '-') ?>
                        </td>
                        <td><?= htmlspecialchars($sale['updated_by_first'] . ' ' . $sale['updated_by_last'] ?? '-') ?>
                        </td>
                        <td>
                            <?php if (!empty($sale['items'])) : ?>
                            <ul>
                                <?php foreach ($sale['items'] as $item) : ?>
                                <li>
                                    <?= htmlspecialchars($item['product_name']) ?>:
                                    <?= $item['quantity'] ?> x ₱<?= number_format($item['unit_price'], 2) ?> =
                                    ₱<?= number_format($item['subtotal_price'], 2) ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php else : ?>
                            No items
                            <?php endif; ?>
                        </td>
                        <?php else : ?>
                        <td><?= htmlspecialchars($sale['period']) ?></td>
                        <td><?= htmlspecialchars($sale['sales_count']) ?></td>
                        <td><?= number_format($sale['total_amount'], 2) ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php else : ?>
                    <tr>
                        <td colspan="<?= $report_type === 'daily' ? 8 : 3 ?>"
                            style="text-align: center; padding: 20px; color: #666;">
                            No sales found for the selected period.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleDateFields() {
    const reportType = document.getElementById('report_type').value;
    document.getElementById('date_field').style.display = reportType === 'daily' ? 'block' : 'none';
    document.getElementById('year_field').style.display = reportType === 'monthly' ? 'block' : 'none';
}

setTimeout(function() {
    let alert = document.querySelector(".floating-alert");
    if (alert) {
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    }
}, 4000);

document.querySelector('form').addEventListener('submit', function(e) {
    const reportType = document.getElementById('report_type').value;
    if (reportType === 'daily') {
        const dateInput = document.getElementById('date').value;
        if (!dateInput) {
            e.preventDefault();
            alert('Please select a date for the daily report.');
        }
    }
});
</script>

<?php if (isset($_SESSION['success'])) : ?>
<div class="alert alert-success alert-dismissible fade show floating-alert" role="alert"
    style="width: 290px !important;">
    <?= $_SESSION['success']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['error'])) : ?>
<div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert"
    style="width: 290px !important;">
    <?= $_SESSION['error']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>
