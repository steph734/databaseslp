<?php
try {
    require_once '../../database/database.php';
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Customer Query
    if (isset($_SESSION['search_results'])) {
        $customer_result = $_SESSION['search_results'];
        unset($_SESSION['search_results']);
    } else {
        $customer_query = "SELECT i.customer_id, i.name, i.contact, i.address, 
                         c.type_name, c.type_id, i.createdbyid, i.createdate,
                         i.updatedbyid, i.updatedate
                         FROM Customer i 
                         JOIN customer_type c ON i.type_id = c.type_id";
        $customer_result = $conn->query($customer_query);
    }

    // Membership Query
    $membership_query = "SELECT m.membership_id, m.customer_id, m.status, 
                        m.date_repairs AS start_date, m.date_renewal AS renewal_date,
                        m.createdbyid, m.createdate, m.updatedbyid, m.updatedate,
                        c.name AS customer_name
                        FROM membership m
                        LEFT JOIN Customer c ON m.customer_id = c.customer_id
                        ORDER BY m.membership_id DESC";
    $membership_stmt = $conn->prepare($membership_query);
    $membership_stmt->execute();
    $membership_result = $membership_stmt->get_result();

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
    <title>Membership & Customer Management</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        /* [Your original CSS remains unchanged, included here for completeness] */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .membership-card, .customer-card {
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
        .membership-info, .customer-info {
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
        .membership-card:hover, .customer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        .membership-card h3, .customer-card h3 {
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
        .status-dropdown {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
            font-weight: bold;
            background: white;
            width: 120px;
            cursor: pointer;
        }
        .status-active { color: #28a745; }
        .status-inactive { color: #dc3545; }
        .status-pending { color: #ffc107; }
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
            <h1>Membership & Customer Management</h1>
            <div class="search-profile">
                <?php include __DIR__ . '/searchbar.php'; ?>
                <?php include __DIR__ . '/profile.php'; ?>
            </div>
        </header>

        <div class="tabs" role="tablist">
            <span class="active" data-tab="memberships" role="tab" aria-selected="true" tabindex="0">Memberships</span>
            <span data-tab="customers" role="tab" aria-selected="false" tabindex="0">Customers</span>
        </div>
        <hr>

        <!-- Memberships Content -->
        <div id="memberships-content" role="tabpanel">
            <div class="header-supplier">
                <button class="btn-add" onclick="toggleAddMemberForm()">
                    <i class="fa fa-add"></i> Add Member
                </button>
            </div>

            <div class="add-form" id="addMemberForm">
                <h3 style="color: #34502b;">Add New Member</h3>
                <form action="../../handlers/createmember.php" method="POST" novalidate>
                    <input type="text" class="form-control" name="customer_id" placeholder="Enter Customer ID" required>
                    <select class="status-dropdown" name="status" required>
                        <option value="" disabled selected>Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                    <input type="date" name="start_date" max="<?php echo date('Y-m-d'); ?>">
                    <input type="date" name="renewal_date" min="<?php echo date('Y-m-d'); ?>">
                    <button type="submit" class="btn-save">Save</button>
                </form>
            </div>

            <div class="card-container">
                <?php if ($membership_result->num_rows === 0): ?>
                    <p>No memberships found.</p>
                <?php else: ?>
                    <?php while ($row = $membership_result->fetch_assoc()): ?>
                        <div class="membership-card">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3>#<?php echo htmlspecialchars($row['membership_id']); ?></h3>
                                <select class="status-dropdown status-<?php echo strtolower($row['status']); ?>"
                                        onchange="updateMembershipStatus(<?php echo $row['membership_id']; ?>, this.value)">
                                    <?php
                                    $statuses = ['active', 'inactive', 'pending'];
                                    foreach ($statuses as $status) {
                                        $selected = $row['status'] === $status ? 'selected' : '';
                                        echo "<option value='$status' $selected class='status-$status'>" . ucfirst($status) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button class="info-toggle" onclick="toggleInfo(this)">
                                <i class="fa fa-circle-info" style="color:rgba(0, 0, 0, 0.87);"></i>
                            </button>
                            <div class="membership-info">
                                <p><strong>Created by:</strong> <?php echo htmlspecialchars($row['createdbyid'] ?? 'N/A'); ?></p>
                                <p><strong>Created:</strong> <?php echo htmlspecialchars($row['createdate']); ?></p>
                                <p><strong>Updated by:</strong> <?php echo htmlspecialchars($row['updatedbyid'] ?? 'N/A'); ?></p>
                                <p><strong>Updated:</strong> <?php echo htmlspecialchars($row['updatedate'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="details">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($row['customer_name']); ?></p>
                                <p><strong>CID:</strong> <?php echo htmlspecialchars($row['customer_id']); ?></p>
                                <p><strong>Start:</strong> <?php echo htmlspecialchars($row['start_date']); ?></p>
                                <p><strong>Renew:</strong> <?php echo htmlspecialchars($row['renewal_date']); ?></p>
                            </div>
                            <div class="actions">
                                <button class="btn btn-edit" 
                                        onclick="loadEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-delete" 
                                        onclick="confirmDelete(<?php echo $row['membership_id']; ?>)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Customers Content -->
        <div id="customers-content" style="display: none;" role="tabpanel">
            <div class="header-supplier">
                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#createCustomer">
                    <i class="fa fa-add"></i> Add Customer
                </button>
            </div>

            <div class="card-container">
                <?php if ($customer_result && $customer_result->num_rows > 0): ?>
                    <?php $customer_result->data_seek(0); ?>
                    <?php while ($row = $customer_result->fetch_assoc()): ?>
                        <div class="customer-card">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3>#<?php echo htmlspecialchars($row['customer_id']); ?></h3>
                            </div>
                            <button class="info-toggle" onclick="toggleInfo(this)">
                                <i class="fa fa-circle-info" style="color:rgba(0, 0, 0, 0.87);"></i>
                            </button>
                            <div class="customer-info">
                                <p><strong>Created by:</strong> <?php echo htmlspecialchars($row['createdbyid'] ?? 'N/A'); ?></p>
                                <p><strong>Created:</strong> <?php echo htmlspecialchars($row['createdate'] ?? 'N/A'); ?></p>
                                <p><strong>Updated by:</strong> <?php echo htmlspecialchars($row['updatedbyid'] ?? 'N/A'); ?></p>
                                <p><strong>Updated:</strong> <?php echo htmlspecialchars($row['updatedate'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="details">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($row['name']); ?></p>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['contact']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($row['type_name']); ?></p>
                            </div>
                            <div class="actions">
                                <button class="btn btn-edit" data-bs-toggle="modal"
                                        data-bs-target="#editCustomer<?php echo $row['customer_id']; ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-delete" data-bs-toggle="modal"
                                        data-bs-target="#deleteCustomer<?php echo $row['customer_id']; ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Customer Edit/Delete Modals (placeholders) -->
                        <div class="modal fade" id="editCustomer<?php echo $row['customer_id']; ?>" tabindex="-1">
                            <!-- Add your edit modal content here -->
                        </div>
                        <div class="modal fade" id="deleteCustomer<?php echo $row['customer_id']; ?>" tabindex="-1">
                            <!-- Add your delete modal content here -->
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No customers found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Membership Edit Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemberModalLabel">Edit Membership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editMemberForm" action="../../handlers/editmember.php" method="POST">
                        <input type="hidden" id="editMembershipId" name="membership_id">
                        <div class="mb-3">
                            <label>Customer</label>
                            <select class="form-control" id="editCustomerId" name="customer_id" required>
                                <?php
                                $customer_result->data_seek(0);
                                while ($customer = $customer_result->fetch_assoc()) {
                                    echo "<option value='{$customer['customer_id']}'>{$customer['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select class="form-control" id="editStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Start Date</label>
                            <input type="date" class="form-control" id="editStartDate" name="start_date">
                        </div>
                        <div class="mb-3">
                            <label>Renewal Date</label>
                            <input type="date" class="form-control" id="editRenewalDate" name="renewal_date">
                        </div>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Create Modal (placeholder) -->
    <div class="modal fade" id="createCustomer" tabindex="-1">
        <!-- Add your create modal content here -->
    </div>

    <?php
    $membership_result->free();
    $customer_result->free();
    $membership_stmt->close();
    $conn->close();
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
    function toggleAddMemberForm() {
        var form = document.getElementById("addMemberForm");
        var button = document.querySelector(".btn-add");
        form.style.display = form.style.display === "none" || form.style.display === "" ? "block" : "none";
        button.innerHTML = form.style.display === "block" ? 
            '<i class="fa fa-times"></i> Close' : 
            '<i class="fa fa-add"></i> Add Member';
    }

    function toggleInfo(button) {
        var infoBox = button.nextElementSibling;
        infoBox.style.display = infoBox.style.display === "none" || infoBox.style.display === "" ? "block" : "none";
    }

    function loadEditModal(membership) {
        document.getElementById("editMembershipId").value = membership.membership_id;
        document.getElementById("editCustomerId").value = membership.customer_id;
        document.getElementById("editStatus").value = membership.status;
        document.getElementById("editStartDate").value = membership.start_date;
        document.getElementById("editRenewalDate").value = membership.renewal_date;
        new bootstrap.Modal(document.getElementById("editMemberModal")).show();
    }

    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this membership?")) {
            window.location.href = "../../handlers/deletemember.php?id=" + id;
        }
    }

    function updateMembershipStatus(membershipId, newStatus) {
        const dropdown = document.querySelector(`select[onchange="updateMembershipStatus(${membershipId}, this.value)"]`);
        dropdown.className = `status-dropdown status-${newStatus}`;
        
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../../handlers/update_membership_status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200 && xhr.responseText !== "success") {
                alert("Failed to update status");
                dropdown.value = dropdown.dataset.oldValue;
                dropdown.className = `status-dropdown status-${dropdown.dataset.oldValue}`;
            }
        };
        dropdown.dataset.oldValue = dropdown.value;
        xhr.send(`membership_id=${membershipId}&status=${newStatus}`);
    }

    document.querySelectorAll('.tabs span').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tabs span').forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            
            document.getElementById('memberships-content').style.display = 
                this.dataset.tab === 'memberships' ? 'block' : 'none';
            document.getElementById('customers-content').style.display = 
                this.dataset.tab === 'customers' ? 'block' : 'none';
        });
    });

    // Autocomplete for customer_id
    $(document).ready(function() {
        $('input[name="customer_id"]').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '../../handlers/get_customers.php',
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