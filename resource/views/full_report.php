<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Unauthorized access!";
    header("Location: ../resource/login.php");
    exit();
}

// Check if report data exists in session
if (!isset($_SESSION['report_data'])) {
    $_SESSION['error'] = "No report data available. Please generate a report first.";
    header("Location: reports.php?page=reports");
    exit();
}

// Retrieve report data from session
$report_data = $_SESSION['report_data'];
$report_type = $report_data['report_type'];
$sales_data = $report_data['sales_data'];
$total_sales = $report_data['total_sales'];
$total_amount = $report_data['total_amount'];
$date = $report_data['date'];
$year = $report_data['year'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Sales Report</title>
    <style>
    body {
        font-family: 'Times New Roman', serif;
        margin: 40px;
        line-height: 1.6;
    }

    .report-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .report-header h1 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .report-header p {
        font-size: 14px;
        color: #555;
    }

    .report-content {
        font-size: 12px;
        margin-bottom: 20px;
    }

    .summary {
        margin-bottom: 20px;
        font-size: 14px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    .right-align {
        text-align: right;
    }

    .btn-print {
        background: #34502b;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 20px;
    }

    .btn-print:hover {
        background: white;
        color: #34502b;
        border: 1px solid #34502b;
    }

    @media print {
        .btn-print {
            display: none;
        }

        body {
            margin: 0;
            padding: 20px;
        }
    }
    </style>
</head>

<body>
    <div class="report-header">
        <h1>Full Sales Report</h1>
        <p>
            <?php if ($report_type === 'daily') : ?>
            Date: <?= htmlspecialchars($date) ?>
            <?php else : ?>
            Year: <?= htmlspecialchars($year) ?>
            <?php endif; ?>
        </p>
    </div>

    <div class="report-content">
        <div class="summary">
            <strong>Summary</strong><br>
            Total Sales: <?= $total_sales ?><br>
            Total Amount: ₱<?= number_format($total_amount, 2) ?>
        </div>

        <?php if ($report_type === 'daily') : ?>
        <?php if (count($sales_data) > 0) : ?>
        <table>
            <thead>
                <tr>
                    <th>Sales ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="right-align">Total Amount (₱)</th>
                    <th>Payment Method</th>
                    <th>Created By</th>
                    <th>Updated By</th>
                    <th>Items</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_data as $sale) : ?>
                <tr>
                    <td><?= htmlspecialchars($sale['sales_id']) ?></td>
                    <td><?= htmlspecialchars($sale['sale_date']) ?></td>
                    <td><?= htmlspecialchars($sale['customer_name'] ?? '-') ?></td>
                    <td class="right-align"><?= number_format($sale['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($sale['payment_method']) ?></td>
                    <td><?= htmlspecialchars($sale['created_by_first'] . ' ' . $sale['created_by_last'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($sale['updated_by_first'] . ' ' . $sale['updated_by_last'] ?? '-') ?></td>
                    <td>
                        <?php if (!empty($sale['items'])) : ?>
                        <?php foreach ($sale['items'] as $item) : ?>
                        <?= htmlspecialchars($item['product_name']) ?>: <?= $item['quantity'] ?> x
                        ₱<?= number_format($item['unit_price'], 2) ?> =
                        ₱<?= number_format($item['subtotal_price'], 2) ?><br>
                        <?php endforeach; ?>
                        <?php else : ?>
                        No items
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
        <p>No sales found for the selected period.</p>
        <?php endif; ?>

        <?php else : ?>
        <?php if (count($sales_data) > 0) : ?>
        <table>
            <thead>
                <tr>
                    <th>Period</th>
                    <th class="right-align">Number of Sales</th>
                    <th class="right-align">Total Amount (₱)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales_data as $sale) : ?>
                <tr>
                    <td><?= htmlspecialchars($sale['period']) ?></td>
                    <td class="right-align"><?= htmlspecialchars($sale['sales_count']) ?></td>
                    <td class="right-align"><?= number_format($sale['total_amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
        <p>No sales found for the selected period.</p>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <button class="btn-print" onclick="window.print()">Print Report</button>
</body>

</html>