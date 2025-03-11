<?php
include '../../database/database.php';

$query = "SELECT m.*, c.customer_id AS customer_id 
FROM membership m 
LEFT JOIN customer c ON m.customer_id = c.customer_id";
$result = $conn->query($query);

// Fetch member customers for dropdown
$memberCustomersQuery = "SELECT customer_id, customer_name FROM customer";
$memberCustomersResult = $conn->query($memberCustomersQuery);
$memberCustomers = [];

if ($memberCustomersResult) {
    while ($row = $memberCustomersResult->fetch_assoc()) {
        $memberCustomers[] = $row;
    }
}
?>

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
                                <label class="form-label">Customer ID</label>
                                <input type="text" name="customerid" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="">Select Status</option>
                                    <?php 
                                    $statusQuery = "SELECT statusid, statusname FROM status";
                                    $statusResult = $conn->query($statusQuery);
                                    while ($statusRow = $statusResult->fetch_assoc()) { ?>
                                        <option value="<?php echo htmlspecialchars($statusRow['statusid']); ?>">
                                            <?php echo htmlspecialchars($statusRow['statusname']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Renewal</label>
                                <input type="date" id="edit_daterenewal" name="daterenewal" class="form-control">
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

        <!-- Membership Table -->
        <table>
            <thead class="member-table" style="color:rgb(29, 28, 28);">
                <tr style="text-align: center !important;">
                    <th><input type="checkbox"></th>
                    <th>Membership ID</th>
                    <th>Customer ID</th>
                    <th>Status</th>
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
                            <td><?php echo htmlspecialchars($row['date_renewal']); ?></td>
                            <td><?php echo htmlspecialchars($row['createdbyid']); ?></td>
                            <td><?php echo htmlspecialchars($row['createdate']); ?></td>
                            <td><?php echo htmlspecialchars($row['updatedbyid']); ?></td>
                            <td><?php echo htmlspecialchars($row['updatedate']); ?></td>
                            <td>
                                <button class="btn btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editMemberModal"
                                    data-id="<?php echo htmlspecialchars($row['membership_id']); ?>"
                                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
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
                        <td colspan="10">No memberships found.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
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


<!-- JavaScript Fixes -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".edit-btn").forEach(button => {
        button.addEventListener("click", function() {
            document.getElementById("edit_membership_id").value = this.getAttribute("data-id");
            document.getElementById("edit_status").value = this.getAttribute("data-status");
            document.getElementById("edit_daterenewal").value = this.getAttribute("data-daterenewal");
        });
    });

    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function() {
            let membershipId = this.getAttribute("data-id");
            if (confirm("Are you sure you want to delete this member?")) {
                fetch("../../handlers/deletemember.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `membership_id=${membershipId}`
                })
                .then(response => response.text())
                .then(() => location.reload())
                .catch(error => console.error("Error:", error));
            }
        });
    });
});
</script>
