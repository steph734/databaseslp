<?php
include '../../database/database.php';

// Check for search results in session, otherwise fetch from database
if (isset($_SESSION['search_results'])) {
    $result = $_SESSION['search_results'];
    unset($_SESSION['search_results']);
} else {
    $query = "SELECT i.customer_id, 
                     i.name,
                     i.contact,
                     i.address,
                     c.type_name,
                     c.type_id,
                     i.createdbyid,
                     i.createdate,
                     i.updatedbyid,
                     i.updatedate,
                     m.membership_id
              FROM Customer i 
              JOIN customer_type c ON i.type_id = c.type_id
              LEFT JOIN membership m ON i.customer_id = m.customer_id";
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers</title>
    <style>
        th {
            background-color: #e6c200 !important;
            color: rgb(22, 21, 21) !important;
            text-align: center !important;
        }
        td {
            text-align: center;
            vertical-align: middle;
        }
        .main-content {
            padding: 20px;
        }
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .customer-table {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .table-controls {
            margin-bottom: 15px;
            text-align: right;
        }
        .btn {
            margin: 2px;
        }
        .disabled-field {
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <header>
            <h1>Customers</h1>
            <div class="search-profile">
                <?php include __DIR__ . '/searchbar.php'; ?>
                <?php include __DIR__ . '/profile.php'; ?>
            </div>
        </header>
        <hr>
        <form method="POST" action="../../handlers/searchcustomer.php">
            <div class="search-container">
                <input type="text" name="customerID" placeholder="Customer ID" class="form-control">
                <input type="text" name="name" placeholder="Name" class="form-control">
                <input type="text" name="contact" placeholder="Contact" class="form-control">
                <input type="text" name="address" placeholder="Address" class="form-control">
                <button type="submit" class="btn btn-success">SEARCH</button>
                <button type="reset" class="btn btn-secondary">CLEAR</button>
            </div>
        </form>

        <div class="customer-table">
            <div class="table-controls">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create">
                    CREATE <i class="fa-solid fa-plus"></i>
                </button>
                <button class="btn btn-danger" id="delete-selected">DELETE SELECTED</button>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Customer Type</th>
                        <th>Created By ID</th>
                        <th>Create Date</th>
                        <th>Updated By ID</th>
                        <th>Update Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <?php
                            // Define REGULAR_TYPE_ID to match your database (same as in JS)
                            $REGULAR_TYPE_ID = '2';
                            
                            // If customer type is Regular, override name, contact, and address with '-'
                            if ($row['type_id'] == $REGULAR_TYPE_ID) {
                                $row['name'] = '-';
                                $row['contact'] = '-';
                                $row['address'] = '-';
                            }
                            ?>
                            <tr>
                                <td><input type="checkbox" class="select-row" value="<?php echo htmlspecialchars($row['customer_id']); ?>"></td>
                                <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['contact'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['address'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['createdbyid'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['createdate'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['updatedbyid'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['updatedate'] ?? '-'); ?></td>
                                <td>
                                    <button class="btn btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#edit<?php echo $row['customer_id']; ?>">Edit</button>
                                    <button class="btn btn-danger delete-row" data-customer-id="<?php echo htmlspecialchars($row['customer_id']); ?>">Delete</button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="edit<?php echo $row['customer_id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Customer</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="../../handlers/updatecustomer.php" method="POST">
                                                <input type="hidden" name="customer_id" value="<?php echo $row['customer_id']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" name="name" id="edit_name_<?php echo $row['customer_id']; ?>" 
                                                        class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" >
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Contact</label>
                                                    <input type="text" name="contact" id="edit_contact_<?php echo $row['customer_id']; ?>" 
                                                        class="form-control" value="<?php echo htmlspecialchars($row['contact']); ?>" >
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Address</label>
                                                    <input type="text" name="address" id="edit_address_<?php echo $row['customer_id']; ?>" 
                                                        class="form-control" value="<?php echo htmlspecialchars($row['address']); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Customer Type</label>
                                                    <select name="customertype" id="edit_customertype_<?php echo $row['customer_id']; ?>" 
                                                        class="form-control" required>
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
                                                    <button type="button" class="btn btn-danger"
                                                        data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="11" class="text-center">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="create" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../../handlers/createcustomer.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="create_name" class="form-control" >
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" name="contact" id="create_contact" class="form-control" >
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" id="create_address" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer Type</label>
                            <select name="customertype" id="create_customertype" class="form-control" required>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const REGULAR_TYPE_ID = '2';  // Must match database
            const MEMBER_TYPE_ID = '1';   // Must match database

            // Toggle fields in modals
            function toggleFields(select, nameInput, contactInput, addressInput) {
                const isRegular = select.value === REGULAR_TYPE_ID;
                
                nameInput.disabled = isRegular;
                contactInput.disabled = isRegular;
                addressInput.disabled = isRegular;
                
                [nameInput, contactInput, addressInput].forEach(input => {
                    input.classList.toggle('disabled-field', isRegular);
                    input.title = isRegular ? 'Disabled for Regular customers' : '';
                    if (isRegular) input.value = ''; // Clear fields for regular
                });
            }

            // Validate membership
            function validateMembership(select, modal) {
                const warningDiv = modal.querySelector('.membership-warning');
                
                if (select.value === MEMBER_TYPE_ID) {
                    fetch('../../handlers/check_membership.php')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.hasMembership) {
                                if (!warningDiv) {
                                    const warning = document.createElement('div');
                                    warning.className = 'membership-warning text-danger';
                                    warning.textContent = 'No membership found - will default to Regular';
                                    select.parentElement.appendChild(warning);
                                }
                                select.value = REGULAR_TYPE_ID;
                                toggleFields(select, 
                                    modal.querySelector('input[name="name"]'),
                                    modal.querySelector('input[name="contact"]'),
                                    modal.querySelector('input[name="address"]')
                                );
                            } else if (warningDiv) {
                                warningDiv.remove();
                            }
                        })
                        .catch(error => console.error('Error:', error));
                } else if (warningDiv) {
                    warningDiv.remove();
                }
            }

            // Setup modal behavior
            function setupModal(modal) {
                const select = modal.querySelector('select[name="customertype"]');
                const nameInput = modal.querySelector('input[name="name"]');
                const contactInput = modal.querySelector('input[name="contact"]');
                const addressInput = modal.querySelector('input[name="address"]');

                if (select && nameInput && contactInput && addressInput) {
                    modal.addEventListener('shown.bs.modal', () => {
                        toggleFields(select, nameInput, contactInput, addressInput);
                        validateMembership(select, modal);
                    });
                    
                    select.addEventListener('change', () => {
                        toggleFields(select, nameInput, contactInput, addressInput);
                        validateMembership(select, modal);
                    });

                    const resetButton = modal.querySelector('button[type="reset"]');
                    if (resetButton) {
                        resetButton.addEventListener('click', () => {
                            setTimeout(() => {
                                toggleFields(select, nameInput, contactInput, addressInput);
                                validateMembership(select, modal);
                            }, 0);
                        });
                    }
                }
            }

            document.querySelectorAll('.modal[id^="edit"]').forEach(setupModal);
            const createModal = document.getElementById('create');
            if (createModal) setupModal(createModal);

            // Select All Checkbox
            const selectAll = document.getElementById('select-all');
            const rowCheckboxes = document.querySelectorAll('.select-row');

            selectAll.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
            });

            // Delete Selected Button (Bulk Delete)
            const deleteSelectedBtn = document.getElementById('delete-selected');
            deleteSelectedBtn.addEventListener('click', function() {
                const selectedIds = Array.from(rowCheckboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);

                if (selectedIds.length === 0) {
                    alert('Please select at least one customer to delete.');
                    return;
                }

                if (confirm(`Are you sure you want to delete ${selectedIds.length} customer(s)?`)) {
                    fetch('../../handlers/deletecustomer.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ customer_ids: selectedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Refresh page after successful deletion
                        } else {
                            alert('Error deleting customers: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting customers.');
                    });
                }
            });

            // Delete Row Button (Single Delete in Actions)
            const deleteRowButtons = document.querySelectorAll('.delete-row');
            deleteRowButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const customerId = this.getAttribute('data-customer-id');
                    if (confirm(`Are you sure you want to delete customer ID ${customerId}?`)) {
                        fetch('../../handlers/deletecustomer.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ customer_ids: [customerId] })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload(); // Refresh page after successful deletion
                            } else {
                                alert('Error deleting customer: ' + data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the customer.');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>