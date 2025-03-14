<?php
try {
    require_once '../../database/database.php';
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Points Query (unchanged)
    $points_query = "SELECT p.points_id, p.membership_id, p.sales_id, p.total_purchase, p.points_amount,
                    m.customer_id, c.name AS customer_name
                    FROM points p
                    LEFT JOIN membership m ON p.membership_id = m.membership_id
                    LEFT JOIN Customer c ON m.customer_id = c.customer_id
                    ORDER BY p.points_id DESC";
    $points_stmt = $conn->prepare($points_query);
    $points_stmt->execute();
    $points_result = $points_stmt->get_result();

    // Points Details Query (unchanged)
    $points_details_query = "SELECT pd.pd_id, pd.points_id, pd.total_points, pd.redeemable_date, 
                           pd.redeemed_amount, pd.createdbyid, pd.createdate, pd.updatedbyid, 
                           pd.updatedate, p.membership_id, c.name AS customer_name
                           FROM points_details pd
                           LEFT JOIN points p ON pd.points_id = p.points_id
                           LEFT JOIN membership m ON p.membership_id = m.membership_id
                           LEFT JOIN Customer c ON m.customer_id = c.customer_id
                           ORDER BY pd.pd_id DESC";
    $points_details_stmt = $conn->prepare($points_details_query);
    $points_details_stmt->execute();
    $points_details_result = $points_details_stmt->get_result();

    // Membership Query (unchanged)
    $membership_query = "SELECT m.membership_id, c.name AS customer_name
                        FROM membership m
                        INNER JOIN Customer c ON m.customer_id = c.customer_id";
    $membership_result = $conn->query($membership_query);

    // Sales Query (adapted from sales code)
    $sales_query = "SELECT s.sales_id, s.sale_date, c.name AS customer_name
                    FROM Sales s
                    LEFT JOIN Customer c ON s.customer_id = c.customer_id
                    GROUP BY s.sales_id
                    ORDER BY s.sales_id DESC";
    $sales_result = $conn->query($sales_query);
    $sales_options = [];
    while ($row = $sales_result->fetch_assoc()) {
        $sales_options[] = $row;
    }

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Database error occurred']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Points Management</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Same styles from your previous points management code */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .points-card, .points-details-card {
            position: relative;
            background: white;
            border-radius: 10px;
            border: 1px solid #34502b;
            padding: 20px;
            transition: 0.3s;
            min-height: 200px;
        }
        .info-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 16px;
            color: #007bff;
            cursor: pointer;
        }
        .points-info, .points-details-info {
            display: none;
            position: absolute;
            top: 35px;
            right: 10px;
            background: rgba(255, 255, 255, 0.8);
            color: #555;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            text-align: left;
            width: 180px;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .points-card:hover, .points-details-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        .points-card h3, .points-details-card h3 {
            margin-bottom: 10px;
            color: #34502b;
        }
        .details p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
        .actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-edit {
            background: white;
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        .btn-edit:hover {
            background: #ffc107;
            color: white;
        }
        .btn-delete {
            background: white;
            color: rgba(255, 0, 25, 0.37);
            border: 1px solid rgba(255, 0, 25, 0.37);
        }
        .btn-delete:hover {
            background: rgb(255, 0, 25);
            color: white;
        }
        .add-form {
            display: none;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .add-form input, .add-form select {
            width: 100%;
            margin-top: 10px;
            padding: 8px;
        }
        .btn-save {
            background-color: #34502b;
            margin-top: 20px;
            padding: 10px;
            width: 100px;
            transition: all 0.2s ease-in-out;
        }
        .btn-save:hover {
            color: #34502b;
            background-color: white;
            border: 1px solid #34502b;
        }
        .btn-add {
            background: #34502b;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: all 0.3s ease-in-out;
        }
        .btn-add:hover {
            transform: translateY(-3px);
        }
        .tabs {
            display: flex;
            gap: 20px;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .tabs span {
            cursor: pointer;
            color: #000;
        }
        .tabs span.active {
            border-bottom: 2px solid #000;
        }
        .ui-autocomplete {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
        }
        .ui-menu-item {
            padding: 5px 10px;
            cursor: pointer;
        }
        .ui-menu-item:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Points Management</h1>
            <div class="search-profile">
                <?php include __DIR__ . '/searchbar.php'; ?>
                <?php include __DIR__ . '/profile.php'; ?>
            </div>
        </header>

        <div class="tabs" role="tablist">
            <span class="active" data-tab="points" role="tab" aria-selected="true" tabindex="0">Points</span>
            <span data-tab="pointsdetails" role="tab" aria-selected="false" tabindex="0">Points Details</span>
        </div>
        <hr>

        <!-- Points Content -->
        <div id="points-content" role="tabpanel">
            <div class="header-supplier">
                <button class="btn-add" onclick="toggleAddPointsForm()">
                    <i class="fa fa-add"></i> Add Points
                </button>
            </div>

            <div class="add-form" id="addPointsForm">
                <h3 style="color: #34502b;">Add New Points</h3>
                <form action="../../handlers/addpoints.php" method="POST" novalidate>
                    <select name="membership_id" required>
                        <option value="" disabled selected>Select Membership</option>
                        <?php while ($member = $membership_result->fetch_assoc()): ?>
                            <option value="<?php echo $member['membership_id']; ?>">
                                <?php echo htmlspecialchars($member['customer_name'] . " (#{$member['membership_id']})"); ?>
                            </option>
                        <?php endwhile; $membership_result->data_seek(0); ?>
                    </select>
                    <select name="sales_id" required>
                        <option value="" disabled selected>Select Sales ID</option>
                        <?php foreach ($sales_options as $sale): ?>
                            <option value="<?php echo $sale['sales_id']; ?>">
                                <?php echo htmlspecialchars("Sale #{$sale['sales_id']} - {$sale['customer_name']} ({$sale['sale_date']})"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" step="0.01" name="total_purchase" placeholder="Total Purchase" required min="0">
                    <input type="number" name="points_amount" placeholder="Points Amount" required min="0">
                    <button type="submit" class="btn-save">Save</button>
                </form>
            </div>

            <div class="card-container">
                <?php if ($points_result->num_rows === 0): ?>
                    <p>No points found.</p>
                <?php else: ?>
                    <?php while ($row = $points_result->fetch_assoc()): ?>
                        <div class="points-card">
                            <h3>#<?php echo htmlspecialchars($row['points_id']); ?></h3>
                            <button class="info-toggle" onclick="toggleInfo(this)">
                                <i class="fa fa-circle-info" style="color:rgba(0, 0, 0, 0.87);"></i>
                            </button>
                            <div class="points-info">
                                <!-- Add audit info if available in your points table -->
                                <p><strong>Created by:</strong> N/A</p>
                                <p><strong>Created:</strong> N/A</p>
                                <p><strong>Updated by:</strong> N/A</p>
                                <p><strong>Updated:</strong> N/A</p>
                            </div>
                            <div class="details">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($row['customer_name'] ?? 'N/A'); ?></p>
                                <p><strong>Membership:</strong> <?php echo htmlspecialchars($row['membership_id']); ?></p>
                                <p><strong>Sales ID:</strong> <?php echo htmlspecialchars($row['sales_id']); ?></p>
                                <p><strong>Purchase:</strong> $<?php echo number_format($row['total_purchase'], 2); ?></p>
                                <p><strong>Points:</strong> <?php echo htmlspecialchars($row['points_amount']); ?></p>
                            </div>
                            <div class="actions">
                                <button class="btn btn-edit" 
                                        onclick="loadEditPointsModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-delete" 
                                        onclick="confirmDeletePoints(<?php echo $row['points_id']; ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Points Details Content -->
        <div id="pointsdetails-content" style="display: none;" role="tabpanel">
            <div class="header-supplier">
                <button class="btn-add" onclick="toggleAddPointsDetailsForm()">
                    <i class="fa fa-add"></i> Add Points Detail
                </button>
            </div>

            <div class="add-form" id="addPointsDetailsForm">
                <h3 style="color: #34502b;">Add New Points Detail</h3>
                <form action="../../handlers/addpointsdetails.php" method="POST" novalidate>
                    <input type="text" name="points_id" placeholder="Points ID" required>
                    <input type="number" name="total_points" placeholder="Total Points" required min="0">
                    <input type="date" name="redeemable_date" required>
                    <input type="number" step="0.01" name="redeemed_amount" placeholder="Redeemed Amount" required min="0">
                    <button type="submit" class="btn-save">Save</button>
                </form>
            </div>

            <div class="card-container">
                <?php if ($points_details_result->num_rows === 0): ?>
                    <p>No points details found.</p>
                <?php else: ?>
                    <?php while ($row = $points_details_result->fetch_assoc()): ?>
                        <div class="points-details-card">
                            <h3>#<?php echo htmlspecialchars($row['pd_id']); ?></h3>
                            <button class="info-toggle" onclick="toggleInfo(this)">
                                <i class="fa fa-circle-info" style="color:rgba(0, 0, 0, 0.87);"></i>
                            </button>
                            <div class="points-details-info">
                                <p><strong>Created by:</strong> <?php echo htmlspecialchars($row['createdbyid'] ?? 'N/A'); ?></p>
                                <p><strong>Created:</strong> <?php echo htmlspecialchars($row['createdate'] ?? 'N/A'); ?></p>
                                <p><strong>Updated by:</strong> <?php echo htmlspecialchars($row['updatedbyid'] ?? 'N/A'); ?></p>
                                <p><strong>Updated:</strong> <?php echo htmlspecialchars($row['updatedate'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="details">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($row['customer_name'] ?? 'N/A'); ?></p>
                                <p><strong>Points ID:</strong> <?php echo htmlspecialchars($row['points_id']); ?></p>
                                <p><strong>Total Points:</strong> <?php echo htmlspecialchars($row['total_points']); ?></p>
                                <p><strong>Redeemable:</strong> <?php echo htmlspecialchars($row['redeemable_date']); ?></p>
                                <p><strong>Redeemed:</strong> $<?php echo number_format($row['redeemed_amount'], 2); ?></p>
                            </div>
                            <div class="actions">
                                <button class="btn btn-edit" 
                                        onclick="loadEditPointsDetailsModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-delete" 
                                        onclick="confirmDeletePointsDetails(<?php echo $row['pd_id']; ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Points Edit Modal -->
    <div class="modal fade" id="editPointsModal" tabindex="-1" aria-labelledby="editPointsModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPointsModalLabel">Edit Points</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPointsForm" action="../../handlers/editpoints.php" method="POST">
                        <input type="hidden" id="editPointsId" name="points_id">
                        <div class="mb-3">
                            <label>Membership ID</label>
                            <input type="text" class="form-control" id="editMembershipId" name="membership_id" required>
                        </div>
                        <div class="mb-3">
                            <label>Sales ID</label>
                            <select class="form-control" id="editSalesId" name="sales_id" required>
                                <?php foreach ($sales_options as $sale): ?>
                                    <option value="<?php echo $sale['sales_id']; ?>">
                                        <?php echo htmlspecialchars("Sale #{$sale['sales_id']} - {$sale['customer_name']} ({$sale['sale_date']})"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Total Purchase</label>
                            <input type="number" step="0.01" class="form-control" id="editTotalPurchase" name="total_purchase" required>
                        </div>
                        <div class="mb-3">
                            <label>Points Amount</label>
                            <input type="number" class="form-control" id="editPointsAmount" name="points_amount" required>
                        </div>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Points Details Edit Modal (unchanged) -->
    <div class="modal fade" id="editPointsDetailsModal" tabindex="-1" aria-labelledby="editPointsDetailsModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPointsDetailsModalLabel">Edit Points Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editPointsDetailsForm" action="../../handlers/editpointsdetails.php" method="POST">
                        <input type="hidden" id="editPdId" name="pd_id">
                        <div class="mb-3">
                            <label>Points ID</label>
                            <input type="text" class="form-control" id="editPointsId" name="points_id" required>
                        </div>
                        <div class="mb-3">
                            <label>Total Points</label>
                            <input type="number" class="form-control" id="editTotalPoints" name="total_points" required>
                        </div>
                        <div class="mb-3">
                            <label>Redeemable Date</label>
                            <input type="date" class="form-control" id="editRedeemableDate" name="redeemable_date" required>
                        </div>
                        <div class="mb-3">
                            <label>Redeemed Amount</label>
                            <input type="number" step="0.01" class="form-control" id="editRedeemedAmount" name="redeemed_amount" required>
                        </div>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    $points_result->free();
    $points_details_result->free();
    $membership_result->free();
    $sales_result->free();
    $points_stmt->close();
    $points_details_stmt->close();
    $conn->close();
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
    function toggleAddPointsForm() {
        var form = document.getElementById("addPointsForm");
        var button = document.querySelector("#points-content .btn-add");
        form.style.display = form.style.display === "none" || form.style.display === "" ? "block" : "none";
        button.innerHTML = form.style.display === "block" ? 
            '<i class="fa fa-times"></i> Close' : 
            '<i class="fa fa-add"></i> Add Points';
    }

    function toggleAddPointsDetailsForm() {
        var form = document.getElementById("addPointsDetailsForm");
        var button = document.querySelector("#pointsdetails-content .btn-add");
        form.style.display = form.style.display === "none" || form.style.display === "" ? "block" : "none";
        button.innerHTML = form.style.display === "block" ? 
            '<i class="fa fa-times"></i> Close' : 
            '<i class="fa fa-add"></i> Add Points Detail';
    }

    function toggleInfo(button) {
        var infoBox = button.nextElementSibling;
        infoBox.style.display = infoBox.style.display === "none" || infoBox.style.display === "" ? "block" : "none";
    }

    function loadEditPointsModal(points) {
        document.getElementById("editPointsId").value = points.points_id;
        document.getElementById("editMembershipId").value = points.membership_id;
        document.getElementById("editSalesId").value = points.sales_id;
        document.getElementById("editTotalPurchase").value = points.total_purchase;
        document.getElementById("editPointsAmount").value = points.points_amount;
        new bootstrap.Modal(document.getElementById("editPointsModal")).show();
    }

    function loadEditPointsDetailsModal(details) {
        document.getElementById("editPdId").value = details.pd_id;
        document.getElementById("editPointsId").value = details.points_id;
        document.getElementById("editTotalPoints").value = details.total_points;
        document.getElementById("editRedeemableDate").value = details.redeemable_date;
        document.getElementById("editRedeemedAmount").value = details.redeemed_amount;
        new bootstrap.Modal(document.getElementById("editPointsDetailsModal")).show();
    }

    function confirmDeletePoints(id) {
        if (confirm("Are you sure you want to delete these points?")) {
            window.location.href = "../../handlers/deletepoints.php?id=" + id;
        }
    }

    function confirmDeletePointsDetails(id) {
        if (confirm("Are you sure you want to delete this points detail?")) {
            window.location.href = "../../handlers/deletepointsdetails.php?pd_id=" + id;
        }
    }

    document.querySelectorAll('.tabs span').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tabs span').forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            
            document.getElementById('points-content').style.display = 
                this.dataset.tab === 'points' ? 'block' : 'none';
            document.getElementById('pointsdetails-content').style.display = 
                this.dataset.tab === 'pointsdetails' ? 'block' : 'none';
        });
    });

    $(document).ready(function() {
        $('input[name="points_id"]').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '../../handlers/get_points.php',
                    method: 'POST',
                    data: { term: request.term },
                    dataType: 'json',
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 1
        });

        // Optional: Add autocomplete for sales_id if desired
        $('input[name="sales_id"]').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '../../handlers/get_sales.php', // You'll need to create this handler
                    method: 'POST',
                    data: { term: request.term },
                    dataType: 'json',
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 1
        });
    });
    </script>
</body>
</html>