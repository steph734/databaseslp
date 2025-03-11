<?php
include '../../database/database.php';

$receiving_query = "SELECT 
    r.receiving_id,
    r.supplier_id,
    s.supplier_name,
    r.receiving_date,
    r.total_quantity,
    r.total_cost,
    r.status,
    GROUP_CONCAT(rd.product_id SEPARATOR ', ') AS product_ids,
    GROUP_CONCAT(p.product_name SEPARATOR ', ') AS product_names,
    GROUP_CONCAT(rd.quantity_furnished SEPARATOR ', ') AS quantities,
    GROUP_CONCAT(rd.unit_cost SEPARATOR ', ') AS unit_costs,
    GROUP_CONCAT(rd.subtotal_cost SEPARATOR ', ') AS subtotal_costs,
    GROUP_CONCAT(rd.condition SEPARATOR ', ') AS conditions,
    rd.createdbyid,
    rd.updatedbyid,
    rd.createdate,
    rd.updatedate
FROM receiving r
JOIN Supplier s ON r.supplier_id = s.supplier_id
JOIN receiving_details rd ON r.receiving_id = rd.receiving_id
JOIN Product p ON rd.product_id = p.product_id
GROUP BY r.receiving_id";
$receiving_result = $conn->query($receiving_query);

if (!$receiving_result) {
    die("Query failed: " . $conn->error); // Debugging line to catch SQL errors
}
?>

<style>
    .receiving-table {
        background: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        overflow-x: auto;
        margin: 20px;
    }

    .table-responsive {
        max-width: 100%;
        overflow-x: auto;
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
        color: rgb(41, 40, 40) !important;
    }

    tr:hover {
        background: #f1f1f1;
    }

    .btn-view {
        background: rgb(255, 255, 255);
        color: #34502b;
        border: 1px solid #34502b;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-view:hover {
        background: #34502b;
        color: white;
    }

    .status-pending {
        color: #ffc107;
        font-weight: bold;
    }

    .status-received {
        color: #28a745;
        font-weight: bold;
    }

    .status-cancelled {
        color: #dc3545;
        font-weight: bold;
    }

    .condition-damaged {
        color: #dc3545;
        font-weight: bold;
    }

    .condition-good {
        color: #28a745;
        font-weight: bold;
    }

    .tabs {
        display: flex;
        gap: 20px;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .tabs a,
    .tabs span {
        cursor: pointer;
        color: #000;
        text-decoration: none;
    }

    .tabs span.active {
        border-bottom: 2px solid #000;
    }
</style>

<div class="main-content">
    <header>
        <h1>Suppliers</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <?php include __DIR__ . '/profile.php'; ?>
        </div>
    </header>
    <!-- Tabs -->
    <div class="tabs">
        <a href="../layout/web-layout.php?page=supplier">My Suppliers</a>
        <span class="active">Receiving</span>
    </div>
    <hr>

    <!-- Receiving Table -->
    <div class="receiving-table">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Receiving ID</th>
                        <th>Supplier</th>
                        <th>Receiving Date</th>
                        <th>Total Quantity</th>
                        <th>Total Cost (₱)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="receiving-table-body">
                    <?php if ($receiving_result->num_rows > 0) : ?>
                        <?php while ($row = $receiving_result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= $row['receiving_id'] ?></td>
                                <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                                <td><?= $row['receiving_date'] ?? '-' ?></td>
                                <td><?= $row['total_quantity'] ?></td>
                                <td><?= number_format($row['total_cost'], 2) ?? '-' ?></td>
                                <td class="status-<?= strtolower($row['status']) ?>"><?= ucfirst($row['status']) ?></td>
                                <td>
                                    <button class="btn btn-view"
                                        onclick="loadReceivingModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                                No receiving records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Receiving Details Modal -->
<div class="modal fade" id="receivingModal" tabindex="-1" aria-labelledby="receivingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receivingModalLabel">Receiving Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label>Receiving ID:</label>
                <p id="view_receiving_id"></p>
                <label>Supplier:</label>
                <p id="view_supplier_name"></p>
                <label>Receiving Date:</label>
                <p id="view_receiving_date"></p>
                <label>Total Quantity:</label>
                <p id="view_total_quantity"></p>
                <label>Total Cost (₱):</label>
                <p id="view_total_cost"></p>
                <label>Status:</label>
                <p id="view_status"></p>
                <label>Products Received:</label>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Quantity Furnished</th>
                            <th>Unit Cost (₱)</th>
                            <th>Subtotal Cost (₱)</th>
                            <th>Condition</th>
                        </tr>
                    </thead>
                    <tbody id="view_receiving_products"></tbody>
                </table>
                <label>Created By:</label>
                <p id="view_createdbyid"></p>
                <label>Create Date:</label>
                <p id="view_createdate"></p>
                <label>Updated By:</label>
                <p id="view_updatedbyid"></p>
                <label>Update Date:</label>
                <p id="view_updatedate"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert"
        style="width: 290px !important;">
        <?= $_SESSION['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<script>
    function loadReceivingModal(receiving) {
        document.getElementById('view_receiving_id').textContent = receiving.receiving_id;
        document.getElementById('view_supplier_name').textContent = receiving.supplier_name;
        document.getElementById('view_receiving_date').textContent = receiving.receiving_date || '-';
        document.getElementById('view_total_quantity').textContent = receiving.total_quantity;
        document.getElementById('view_total_cost').textContent = Number(receiving.total_cost).toFixed(2) || '-';
        document.getElementById('view_status').textContent = receiving.status ? receiving.status.charAt(0).toUpperCase() +
            receiving.status.slice(1) : '-';
        document.getElementById('view_createdbyid').textContent = receiving.createdbyid || '-';
        document.getElementById('view_createdate').textContent = receiving.createdate || '-';
        document.getElementById('view_updatedbyid').textContent = receiving.updatedbyid || '-';
        document.getElementById('view_updatedate').textContent = receiving.updatedate || '-';

        const productIds = receiving.product_ids ? receiving.product_ids.split(', ') : [];
        const productNames = receiving.product_names ? receiving.product_names.split(', ') : [];
        const quantities = receiving.quantities ? receiving.quantities.split(', ') : [];
        const unitCosts = receiving.unit_costs ? receiving.unit_costs.split(', ') : [];
        const subtotalCosts = receiving.subtotal_costs ? receiving.subtotal_costs.split(', ') : [];
        const conditions = receiving.conditions ? receiving.conditions.split(', ') : [];

        const tbody = document.getElementById('view_receiving_products');
        tbody.innerHTML = ''; // Clear previous content

        for (let i = 0; i < productIds.length; i++) {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${productIds[i] || '-'}</td>
                <td>${productNames[i] || '-'}</td>
                <td>${quantities[i] || '-'}</td>
                <td>${Number(unitCosts[i]).toFixed(2) || '-'}</td>
                <td>${Number(subtotalCosts[i]).toFixed(2) || '-'}</td>
                <td class="condition-${conditions[i]?.toLowerCase() || ''}">${conditions[i] || '-'}</td>
            `;
            tbody.appendChild(row);
        }

        var modal = new bootstrap.Modal(document.getElementById("receivingModal"));
        modal.show();
    }

    setTimeout(function() {
        let alert = document.querySelector(".floating-alert");
        if (alert) {
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }
    }, 4000);
</script>