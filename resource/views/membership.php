<?php
include '../../database/database.php';

// Fetch membership data
$query = "SELECT m.*, s.statusname FROM membership m 
          LEFT JOIN status s ON m.status = s.statusid";
$result = $conn->query($query);

// Fetch member customers for dropdown - This can be removed if not needed elsewhere
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
        <button class="btn btn-danger my-3">
            DELETE <i class="fa-solid fa-trash"></i>
        </button>   

        <!-- Add Membership Modal - Modified Section -->
        <div class="modal fade" id="addMemberModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../handlers/createmember.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Membership ID</label>
                                <input type="text" name="membershipid" class="form-control" required>
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
                                <input type="date" name="daterenewal" class="form-control" required>
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

        <!-- Rest of the code remains unchanged -->
        <!-- Membership Table -->
        <table>
            <thead class="member-table" style="color:rgb(29, 28, 28);">
                <tr style="text-align: center !important;">
                    <th><input type="checkbox"></th>
                    <th>Membership ID</th>
                    <th>Status</th>
                    <th>Date Renewal</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                    <th>Updated By</th>
                    <th>Updated Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <style>
                .btn-edit {
                    background-color: transparent;
                    color: black !important;
                    border: none;
                    text-decoration: underline;
                    cursor: pointer;
                }
                .btn-edit:hover {
                    color: yellow !important;
                    text-decoration-color: yellow;
                    text-decoration-thickness: 2px;
                }
                .btn-delete {
                    background-color: transparent;
                    color: black;
                    border: none;
                    text-decoration: underline;
                    cursor: pointer;
                    margin-left: 15px;
                }
                .btn-delete:hover {
                    color: red !important;
                    text-decoration-color: red;
                    text-decoration-thickness: 2px;
                }
                .btn-restrict {
                    background-color: transparent;
                    color: black;
                    border: none;
                    text-decoration: underline;
                    cursor: pointer;
                    margin-left: 15px;
                }
                .btn-restrict:hover {
                    color: red !important;
                    text-decoration-color: red;
                    text-decoration-thickness: 2px;
                }
            </style>
            <tbody>
                <?php if ($result && $result->num_rows > 0) { ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><?php echo htmlspecialchars($row['membership_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['statusname']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_renewal']); ?></td>
                            <td><?php echo htmlspecialchars($row['createdbyid']); ?></td>
                            <td><?php echo htmlspecialchars($row['createdate']); ?></td>
                            <td><?php echo htmlspecialchars($row['updatedbyid']); ?></td>
                            <td><?php echo htmlspecialchars($row['updatedate']); ?></td>
                            <td>
                                <button class="btn-edit" data-bs-toggle="modal"
                                    data-bs-target="#editMemberModal"
                                    data-id="<?php echo htmlspecialchars($row['membership_id']); ?>"
                                    data-customer="<?php echo htmlspecialchars($row['customer_id']); ?>"
                                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                    data-daterenewal="<?php echo htmlspecialchars($row['date_renewal']); ?>">
                                    EDIT
                                </button>
                                <button class="btn-delete"
                                    data-id="<?php echo htmlspecialchars($row['membership_id']); ?>">
                                    DELETE
                                </button>
                                <button class="btn-restrict">
                                    RESTRICT
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="9">No memberships found.</td>
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
                        <label class="form-label">Customer</label>
                        <select name="customer_id" id="edit_customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($memberCustomers as $customer) { ?>
                                <option value="<?php echo htmlspecialchars($customer['customer_id']); ?>">
                                    <?php echo htmlspecialchars($customer['customer_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-control" required>
                            <option value="">Select Status</option>
                            <?php 
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
                        <input type="date" id="edit_daterenewal" name="daterenewal" class="form-control" required>
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

<!-- JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Edit button functionality
    document.querySelectorAll(".btn-edit").forEach(button => {
        button.addEventListener("click", function() {
            const modal = document.getElementById('editMemberModal');
            modal.querySelector('#edit_membership_id').value = this.getAttribute("data-id");
            modal.querySelector('#edit_customer_id').value = this.getAttribute("data-customer");
            modal.querySelector('#edit_status').value = this.getAttribute("data-status");
            modal.querySelector('#edit_daterenewal').value = this.getAttribute("data-daterenewal");
        });
    });

    // Delete button functionality
    document.querySelectorAll(".btn-delete").forEach(button => {
        button.addEventListener("click", function() {
            const membershipId = this.getAttribute("data-id");
            if (confirm("Are you sure you want to delete this membership?")) {
                fetch("../../handlers/deletemember.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `membership_id=${encodeURIComponent(membershipId)}`
                })
                .then(response => response.text())
                .then(() => location.reload())
                .catch(error => console.error("Error:", error));
            }
        });
    });

    // Clear search button
    document.querySelector(".clear-btn").addEventListener("click", function() {
        document.getElementById("membership-id").value = "";
        document.getElementById("status").value = "";
    });
});
</script>