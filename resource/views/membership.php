<?php
include '../../database/database.php';

$query = "SELECT * FROM membership";
$result = $conn->query($query);
?>

<div class="main-content">
    <header>
        <h1>Membership</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
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
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pointsmodal">
            POINTS <i class="fa-solid fa-star"></i>
        </button>
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
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
                            <div class="modal-footer">
                                <button type="submit" name="submit" class="btn btn-success">Submit</button>
                                <button type="reset" class="btn btn-primary">Clear</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
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
            <thead>
                <tr>
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
                    <td><?php echo htmlspecialchars($row['date_start']); ?></td>
                    <td><?php echo htmlspecialchars($row['date_renewal']); ?></td>
                    <td><?php echo htmlspecialchars($row['createdbyid']); ?></td>
                    <td><?php echo htmlspecialchars($row['createdate']); ?></td>
                    <td><?php echo htmlspecialchars($row['updatedbyid']); ?></td>
                    <td><?php echo htmlspecialchars($row['updatedate']); ?></td>
                    <td>
                        <br>
                        <button class="btn btn-warning edit-btn" data-bs-toggle="modal" data-bs-target="#editMemberModal" 
                            data-id="<?php echo htmlspecialchars($row['membership_id']); ?>"
                            data-status="<?php echo htmlspecialchars($row['status']); ?>"
                            data-startdate="<?php echo htmlspecialchars($row['date_start']); ?>"
                            data-daterenewal="<?php echo htmlspecialchars($row['date_renewal']); ?>">
                            EDIT <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn btn-danger delete-btn" data-id="<?php echo htmlspecialchars($row['membership_id']); ?>">
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

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("edit_membership_id").value = this.getAttribute("data-id");
            document.getElementById("edit_status").value = this.getAttribute("data-status");
            document.getElementById("edit_startdate").value = this.getAttribute("data-startdate");
            document.getElementById("edit_daterenewal").value = this.getAttribute("data-daterenewal");
        });
    });

    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function () {
            let membershipId = this.getAttribute("data-id");
            if (confirm("Are you sure you want to delete this member?")) {
                fetch("../../handlers/deletemember.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
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
