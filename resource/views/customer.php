<?php 
include '../../database/database.php';


// Fetch customers
$query = "SELECT * FROM Customer";
$result = $conn->query($query);
?>

<div class="main-content">
    <header>
        <h1>Customers</h1>
        <div class="search-profile">
            <?php include __DIR__ . '/searchbar.php'; ?>
            <i class="fa-solid fa-user" style="margin-left: 20px;"></i>
        </div>
    </header>
    <form method="POST" action="../../handlers/searchcustomer.php">
        <div class="search-container">
            <input type="text" name="customerID" placeholder="Customer ID">
            <input type="text" name="name" placeholder="Name">
            <input type="text" name="contact" placeholder="Contact">
            <input type="text" name="address" placeholder="Address">
            <button type="submit" class="btn btn-success"  onclick="window.location.href=window.location.href">SEARCH</button>
        <button class="btn btn-secondary">CLEAR</button>
</form>
    </div>

    <div class="customer-table">
        <div class="table-controls">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create">CREATE <i class="fa-solid fa-plus"></i></button>
            
            <div class="modal" id="create">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form action="../../handlers/createcustomer.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contact</label>
                                    <input type="text" name="contact" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="address" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Membership Status</label>
                                    <select name="is_member" class="form-control" required>
                                        <option value="">Select Membership</option>
                                        <option value="1">Member</option>
                                        <option value="0">Non-Member</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Customer Type</label>
                                    <select name="customertype" class="form-control" required>
                                        <option value="">Select Customer Type</option>
                                        <?php
                                        $typeResult = $conn->query("SELECT type_id, type_name FROM Customer_Type");
                                        while ($row = $typeResult->fetch_assoc()) {
                                            echo "<option value='{$row['type_id']}'>{$row['type_name']}</option>";
                                        }
                                        ?>
                                    </select>
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

        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>CustomerID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Is_member</th>
                    <th>TypeID</th>
                    <th>CreatedByID</th>
                    <th>CreateDate</th>
                    <th>UpdateByID</th>
                    <th>UpdateDate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0) { ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo ($row['is_member'] ? 'Member' : 'Non-Member'); ?></td>
                        <td><?php echo htmlspecialchars($row['type_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['createdbyid']); ?></td>
                        <td><?php echo htmlspecialchars($row['createdate']); ?></td>
                        <td><?php echo htmlspecialchars($row['updatedbyid']); ?></td>
                        <td><?php echo htmlspecialchars($row['updatedate']); ?></td>
                        <td>
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit<?php echo $row['customer_id']; ?>">Edit</button>
                            <br>
                            
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete<?php echo $row['customer_id']; ?>">Delete</button>

                        </td>
                    </tr>

                    <!--Delete Modal -->
                    <div class="modal" id="delete<?php echo $row['customer_id']; ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this customer?</p>
                <form action="../../handlers/deletecustomer.php" method="POST">
                    <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>
    </div>

                    <!-- Edit Modal -->
                    <div class="modal" id="edit<?php echo $row['customer_id']; ?>">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="../../handlers/updatecustomer.php" method="POST">
                                        <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Name</label>
                                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Contact</label>
                                            <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($row['contact']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Address</label>
                                            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($row['address']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Membership Status</label>
                                            <select name="is_member" class="form-control" required>
                                                <option value="1" <?php echo $row['is_member'] == 1 ? 'selected' : ''; ?>>Member</option>
                                                <option value="0" <?php echo $row['is_member'] == 0 ? 'selected' : ''; ?>>Non-Member</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Customer Type</label>
                                            <select name="customertype" class="form-control" required>
                                                <?php
                                                $typeResult = $conn->query("SELECT type_id, type_name FROM Customer_Type");
                                                while ($typeRow = $typeResult->fetch_assoc()) {
                                                    $selected = ($row['type_id'] == $typeRow['type_id']) ? 'selected' : '';
                                                    echo "<option value='{$typeRow['type_id']}' $selected>{$typeRow['type_name']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Update</button>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="12" class="text-center">No records found.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
