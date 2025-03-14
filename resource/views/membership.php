<?php
try {
    require_once '../../database/database.php';
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Query for ALL customers (Customer tab)
    if (isset($_SESSION['search_results'])) {
        $all_customer_result = $_SESSION['search_results'];
        unset($_SESSION['search_results']);
    } else {
        $all_customer_query = "SELECT i.customer_id, i.name, i.contact, i.address, 
                             c.type_name, c.type_id, i.createdbyid, i.createdate,
                             i.updatedbyid, i.updatedate
                             FROM Customer i 
                             JOIN customer_type c ON i.type_id = c.type_id";
        $all_customer_result = $conn->query($all_customer_query);
    }

    // Query for member customers only (Member tab dropdown)
    $member_customer_query = "SELECT i.customer_id, i.name, i.contact, i.address, 
                            c.type_name, c.type_id, i.createdbyid, i.createdate,
                            i.updatedbyid, i.updatedate
                            FROM Customer i 
                            JOIN customer_type c ON i.type_id = c.type_id
                            INNER JOIN membership m ON i.customer_id = m.customer_id";
    $member_customer_result = $conn->query($member_customer_query);

    // Membership Query
    $membership_query = "SELECT m.membership_id, m.customer_id, m.status, 
                    m.date_repairs AS start_date, m.date_renewal AS renewal_date,
                    m.createdbyid, m.createdate, m.updatedbyid, m.updatedate,
                    c.name AS customer_name
                    FROM membership m
                    INNER JOIN Customer c ON m.customer_id = c.customer_id
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
        .status-inactive { color:  #808080; }
        .status-blocked { color: #dc3545; }
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
            <h1>Membership</h1>
            <div class="search-profile">
                <?php include __DIR__ . '/searchbar.php'; ?>
                <?php include __DIR__ . '/profile.php'; ?>
            </div>
        </header>

    <!-- Search Bar -->
    <div class="search-container-membership">
        <input type="text" id="membership-id" placeholder="Membership ID">
        <input type="text" id="customer-id" placeholder="Customer ID">
        <input type="text" id="status" placeholder="Status">
        <button class="search-btn">SEARCH</button>
        <button class="clear-btn">CLEAR</button>
    </div>

    <div class="membership-table">
        <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#pointsmodal">
            POINTS <i class="fa-solid fa-star"></i>
        </button>
        <button class="btn btn-secondary my-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            CREATE NEW <i class="fa-solid fa-add"></i>
        </button>

        <!-- Add Membership Modal -->
        <div class="modal fade" id="addMemberModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Member</h5>
                    </div>
                    <div class="modal-body">
                        <form action="../../handlers/createmember.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" name="status" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="startdate" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Renewal</label>
                                <input type="date" name="daterenewal" class="form-control">
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

        <!-- Edit Membership Modal -->
        <div class="modal fade" id="editMemberModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Membership</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../handlers/editmember.php" method="POST">
                            <input type="hidden" id="edit_membership_id" name="membership_id">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" id="edit_status" name="status" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" id="edit_startdate" name="startdate" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Renewal</label>
                                <input type="date" id="edit_daterenewal" name="daterenewal" class="form-control">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="edit_member" class="btn btn-success">Update</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Membership Table -->
        <table>
            <thead class="member-table" style="color:rgb(29, 28, 28); ">
                <tr style="text-align: center !important;">
                    <th><input type="checkbox"></th>
                    <th>Membership ID</th>
                    <th>Customer ID</th>
                    <th>Status</th>
                    <th>Date Start</th>
                    <th>Date Renewal</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                    <th>Updated By</th>
                    <th>Updated Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0) { ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><?php echo htmlspecialchars($row['membership_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_repairs']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_renewal']); ?></td>
                            <td><?php echo htmlspecialchars($row['createdbyid']); ?></td>
                            <td><?php echo htmlspecialchars($row['createdate']); ?></td>
                            <td><?php echo htmlspecialchars($row['updatedbyid']); ?></td>
                            <td><?php echo htmlspecialchars($row['updatedate']); ?></td>
                            <td>
                                <br>
                                <button class="btn btn-warning edit-btn" data-bs-toggle="modal"
                                    data-bs-target="#editMemberModal"
                                    data-id="<?php echo htmlspecialchars($row['membership_id']); ?>"
                                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                    data-startdate="<?php echo htmlspecialchars($row['date_repairs']); ?>"
                                    data-daterenewal="<?php echo htmlspecialchars($row['date_renewal']); ?>">
                                    EDIT <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-danger delete-btn"
                                    data-id="<?php echo htmlspecialchars($row['membership_id']); ?>">
                                    DELETE <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="11">No memberships found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="pointsmodal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 800px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Member Points</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm" action="../../handlers/addcategory_handler.php" method="POST">
                    <div class="mb-3">
                        <label for="points_id" class="form-label">Point ID</label>
                        <input type="text" class="form-control" id="points_id" name="points_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="total_purchase" class="form-label">Total Purchase</label>
                        <input type="text" class="form-control" id="total_purchase" name="total_purchase" required>
                    </div>
                    <div class="mb-3">
                        <label for="points_amount" class="form-label">Points Amount</label>
                        <input type="text" class="form-control" id="points_amount" name="points_amount" required>
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                </form>
                <br>

                <table class="category-table">
                    <thead>
                        <tr>
                            <th>PointsID</th>
                            <th>MembershipID</th>
                            <th>SalesID</th>
                            <th>TotalPurchase</th>
                            <th>PointsAmount</th>
                            <th>Action</th>

                        </tr>
                    </thead>
            </div>


            <!--Edit script-->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelectorAll(".edit-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            document.getElementById("edit_membership_id").value = this.getAttribute(
                                "data-id");
                            document.getElementById("edit_status").value = this.getAttribute(
                                "data-status");
                            document.getElementById("edit_startdate").value = this.getAttribute(
                                "data-startdate");
                            document.getElementById("edit_daterenewal").value = this.getAttribute(
                                "data-daterenewal");
                        });
                    });

                    document.querySelectorAll(".delete-btn").forEach(button => {
                        button.addEventListener("click", function() {
                            let membershipId = this.getAttribute("data-id");
                            if (confirm("Are you sure you want to delete this member?")) {
                                fetch("../../handlers/deletemember.php", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded"
                                        },
                                        body: `membership_id=${membershipId}`
                                    })
                                    .then(response => response.text())
                                    .then(data => {
                                        alert("Member deleted successfully!");
                                        location.reload();
                                    })
                                    .catch(error => console.error("Error:", error));
                            }
                        });
                    });
                });
            </script>