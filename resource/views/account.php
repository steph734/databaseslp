<?php
include '../../database/database.php';

// Pagination settings for Users
$users_rows_per_page = isset($_GET['users_rows']) && in_array($_GET['users_rows'], [5, 10, 25, 50]) ? (int)$_GET['users_rows'] : 5;
$users_page = isset($_GET['users_page']) && is_numeric($_GET['users_page']) ? (int)$_GET['users_page'] : 1;
$users_offset = ($users_page - 1) * $users_rows_per_page;

// Fetch total number of users for pagination
$users_total_query = "SELECT COUNT(*) as total FROM admin";
$users_total_result = $conn->query($users_total_query);
$users_total_row = $users_total_result->fetch_assoc();
$users_total_records = $users_total_row['total'];
$users_total_pages = ceil($users_total_records / $users_rows_per_page);

// Users Query with pagination
$whereConditions = [];
$orderClause = "ORDER BY a.admin_id DESC"; // Default ordering

if (isset($_GET['search']) && $_GET['search'] === '1') {
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $whereConditions[] = "a.admin_id = '" . $conn->real_escape_string($_GET['user_id']) . "'";
    }
    if (isset($_GET['username']) && !empty($_GET['username'])) {
        $whereConditions[] = "a.username LIKE '%" . $conn->real_escape_string($_GET['username']) . "%'";
    }
    if (isset($_GET['role']) && !empty($_GET['role'])) {
        $whereConditions[] = "a.role = '" . $conn->real_escape_string($_GET['role']) . "'";
    }
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $whereConditions[] = "a.status = '" . $conn->real_escape_string($_GET['status']) . "'";
    }
}

if (isset($_GET['order_by']) && !empty($_GET['order_by'])) {
    list($column, $direction) = explode('|', $_GET['order_by']);
    $column = $conn->real_escape_string($column);
    $direction = strtoupper($conn->real_escape_string($direction));
    if (in_array($column, ['admin_id', 'username', 'role', 'status']) && in_array($direction, ['ASC', 'DESC'])) {
        $orderClause = "ORDER BY a.$column $direction";
    }
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

$usersQuery = "SELECT a.admin_id, a.username, a.role, a.first_name, a.last_name, a.middle_name, a.email, a.phonenumber, a.status 
               FROM admin a 
               $whereClause 
               $orderClause 
               LIMIT ? OFFSET ?";
$stmt = $conn->prepare($usersQuery);
$stmt->bind_param("ii", $users_rows_per_page, $users_offset);
$stmt->execute();
$usersResult = $stmt->get_result();

if (!$usersResult) {
    echo "Users Query failed: " . $conn->error;
    exit;
}

// Pagination settings for Audit Log
$audit_rows_per_page = isset($_GET['audit_rows']) && in_array($_GET['audit_rows'], [5, 10, 25, 50]) ? (int)$_GET['audit_rows'] : 5;
$audit_page = isset($_GET['audit_page']) && is_numeric($_GET['audit_page']) ? (int)$_GET['audit_page'] : 1;
$audit_offset = ($audit_page - 1) * $audit_rows_per_page;

// Fetch total number of audit logs for pagination
$audit_total_query = "SELECT COUNT(*) as total FROM AuditLog";
$audit_total_result = $conn->query($audit_total_query);
$audit_total_row = $audit_total_result->fetch_assoc();
$audit_total_records = $audit_total_row['total'];
$audit_total_pages = ceil($audit_total_records / $audit_rows_per_page);

// Audit Log Query with pagination
$auditWhereConditions = [];
$auditOrderClause = "ORDER BY a.timestamp DESC";

if (isset($_GET['audit_search']) && $_GET['audit_search'] === '1') {
    if (isset($_GET['audit_user_id']) && !empty($_GET['audit_user_id'])) {
        $auditWhereConditions[] = "a.admin_id = '" . $conn->real_escape_string($_GET['audit_user_id']) . "'";
    }
    if (isset($_GET['action']) && !empty($_GET['action'])) {
        $auditWhereConditions[] = "a.action LIKE '%" . $conn->real_escape_string($_GET['action']) . "%'";
    }
}

if (isset($_GET['audit_order_by']) && !empty($_GET['audit_order_by'])) {
    list($column, $direction) = explode('|', $_GET['audit_order_by']);
    $column = $conn->real_escape_string($column);
    $direction = strtoupper($conn->real_escape_string($direction));
    if (in_array($column, ['log_id', 'admin_id', 'action', 'timestamp']) && in_array($direction, ['ASC', 'DESC'])) {
        $auditOrderClause = "ORDER BY a.$column $direction";
    }
}

$auditWhereClause = !empty($auditWhereConditions) ? "WHERE " . implode(" AND ", $auditWhereConditions) : "";

$auditQuery = "SELECT a.log_id, a.admin_id, ad.username, a.action, a.description, a.timestamp 
               FROM AuditLog a 
               LEFT JOIN admin ad ON a.admin_id = ad.admin_id 
               $auditWhereClause 
               $auditOrderClause 
               LIMIT ? OFFSET ?";
$auditStmt = $conn->prepare($auditQuery);
$auditStmt->bind_param("ii", $audit_rows_per_page, $audit_offset);
$auditStmt->execute();
$auditResult = $auditStmt->get_result();

if (!$auditResult) {
    echo "Audit Query failed: " . $conn->error;
    exit;
}
?>

<style>
html,
body {
    overflow-x: hidden;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

.main-content {
    margin-left: 250px;
    width: calc(100% - 250px);
    padding: 20px;
    overflow: hidden;
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
}

.tabs {
    display: flex;
    gap: 20px;
    padding: 10px 0;
}

.tabs span {
    padding: 8px 15px;
    cursor: pointer;
    font-size: 16px;
    color: #555;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.tabs span.active {
    background: #34502b;
    color: white;
    font-weight: bold;
}

.tabs span:hover:not(.active) {
    background: #e0e0e0;
    color: #34502b;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.search-container {
    margin: 20px 0;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-container input,
.search-container select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.search-btn,
.clear-btn {
    padding: 8px 15px;
    border: none;
    background: #34502b;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 12px;
}

.clear-btn {
    background: white;
    color: #34502b;
    border: 1px solid #34502b;
    width: 70px;
}

.accounts-table,
.audit-table {
    background: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    overflow-x: auto;
}

.table-controls {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.create-btn {
    background: #34502b;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

.create-btn:hover {
    background: white;
    color: #34502b;
    border: 1px solid #34502b;
}

.table-responsive {
    max-width: 100%;
    overflow-x: auto;
}

th {
    color: rgb(41, 40, 40) !important;
    text-align: center !important;
    padding: 10px;
}

th,
td {
    text-align: center;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.btn {
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
}

.btn-warning {
    background: #ffc107;
}

.btn-danger {
    background: #dc3545;
}

.modal-content {
    padding: 20px;
    border-radius: 5px;
}

.modal-header {
    color: #34502b;
    padding: 15px;
    border-radius: 5px 5px 0 0;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
}

.custom-select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    background-color: white;
    cursor: pointer;
    width: 50px;
}

.rows-per-page-select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    background-color: white;
    cursor: pointer;
    width: 100px;
}

.select2-container {
    width: 50px !important;
}

.select2-selection__rendered {
    display: flex;
    align-items: center;
    padding: 0 5px;
    justify-content: center;
}

.select2-selection__rendered i {
    font-size: 16px;
}

.custom-select:focus,
.rows-per-page-select:focus {
    outline: none;
    border-color: #34502b;
    box-shadow: 0 0 5px rgba(52, 80, 43, 0.5);
}

.select2-dropdown {
    width: 200px !important;
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.select2-results__option {
    padding: 8px;
    display: flex;
    align-items: center;
}

.floating-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    width: auto !important;
    padding-right: 2.5rem !important;
}

.alert-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-link.toggle-message {
    padding: 0;
    font-size: 0.9em;
    color: #fff;
    text-decoration: underline;
}

.btn-link.toggle-message:hover {
    color: #ddd;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px 0;
    gap: 10px;
    flex-wrap: wrap;
    /* Ensure pagination wraps on smaller screens */
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-decoration: none;
    color: #34502b;
    font-size: 14px;
    transition: all 0.3s ease;
}

.pagination a:hover {
    background: #34502b;
    color: white;
    border-color: #34502b;
}

.pagination .current {
    background: #34502b;
    color: white;
    border-color: #34502b;
}

.pagination .disabled {
    color: #aaa;
    border-color: #ddd;
    pointer-events: none;
}

.pagination .ellipsis {
    padding: 8px 12px;
    color: #aaa;
    pointer-events: none;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 200px;
        width: calc(100% - 200px);
    }

    .search-container {
        flex-direction: column;
    }

    .tabs {
        flex-direction: column;
        gap: 10px;
    }

    .pagination {
        gap: 5px;
        /* Reduce gap on smaller screens */
    }
}

@media (max-width: 500px) {
    .main-content {
        margin-left: 0;
        width: 100%;
    }

    .pagination a,
    .pagination span {
        padding: 6px 10px;
        /* Slightly smaller padding for smaller screens */
        font-size: 12px;
    }
}

.btn-status-toggle {
    padding: 5px 10px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 12px;
    cursor: pointer;
}

.btn-deactivate {
    color: #dc3545;
    border: 1px solid #dc3545;
    background: white;
}

.btn-activate {
    color: #28a745;
    border: 1px solid #28a745;
    background: white;
}

.btn-deactivate:hover {
    background: #dc3545;
    color: white;
}

.btn-activate:hover {
    background: #28a745;
    color: white;
}
</style>

<div class="main-content">
    <header>
        <h1>Account Management</h1>
    </header>

    <!-- Tabs -->
    <div class="tabs">
        <span class="active" data-tab="accounts">Accounts</span>
        <span data-tab="audit">Audit Log</span>
    </div>
    <hr>

    <!-- Accounts Tab Content -->
    <div class="tab-content active" id="accounts-tab">
        <div class="accounts-table">
            <h3 style="color: #34502b;">Admins</h3>
            <div class="search-container">
                <input type="text" id="searchUserID" placeholder="Admin ID">
                <input type="text" id="searchUsername" placeholder="Username">
                <select id="searchRole">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                </select>
                <select id="searchStatus">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button class="search-btn" onclick="searchUsers()">SEARCH</button>
                <button class="clear-btn" onclick="clearUserSearch()">CLEAR</button>
            </div>

            <div class="table-controls">
                <div>
                    <button class="create-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fa-solid fa-user-plus"></i> ADD ADMIN
                    </button>
                    <select id="userOrderBy" class="custom-select" onchange="applyUserFilters()">
                        <option value="admin_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">ID (Descending)</option>
                        <option value="admin_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">ID (Ascending)</option>
                        <option value="username|ASC" data-icon="fa-solid fa-arrow-up-a-z">Username (A-Z)</option>
                        <option value="username|DESC" data-icon="fa-solid fa-arrow-down-z-a">Username (Z-A)</option>
                        <option value="status|ASC" data-icon="fa-solid fa-arrow-up-a-z">Status (A-Z)</option>
                        <option value="status|DESC" data-icon="fa-solid fa-arrow-down-z-a">Status (Z-A)</option>
                    </select>
                </div>
                <select id="usersRowsPerPage" class="rows-per-page-select" onchange="updateUsersRowsPerPage()">
                    <option value="5" <?= $users_rows_per_page == 5 ? 'selected' : '' ?>>5 pages</option>
                    <option value="10" <?= $users_rows_per_page == 10 ? 'selected' : '' ?>>10 pages</option>
                    <option value="25" <?= $users_rows_per_page == 25 ? 'selected' : '' ?>>25 pages</option>
                    <option value="50" <?= $users_rows_per_page == 50 ? 'selected' : '' ?>>50 pages</option>
                </select>
            </div>

            <div class="table-responsive rounded-3" id="users-table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($usersResult->num_rows > 0) : ?>
                        <?php while ($row = $usersResult->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row['admin_id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phonenumber']) ?></td>
                            <td><?= $row['role'] ?></td>
                            <td><?= ucfirst($row['status']) ?></td>
                            <td>
                                <?php if ($row['status'] === 'active') : ?>
                                <button class="btn-status-toggle btn-deactivate"
                                    onclick="toggleUserStatus(<?= $row['admin_id'] ?>, 'inactive')">
                                    <i class="fa-solid fa-ban"></i> Deactivate
                                </button>
                                <?php else : ?>
                                <button class="btn-status-toggle btn-activate"
                                    onclick="toggleUserStatus(<?= $row['admin_id'] ?>, 'active')">
                                    <i class="fa-solid fa-check"></i> Activate
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else : ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px; color: #666;">No admin records
                                found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Users Pagination -->
            <div class="pagination" id="users-pagination">
                <?php
                $max_visible_pages = 5; // Show 5 page numbers around the current page
                $half_range = floor($max_visible_pages / 2);

                // Calculate the range of pages to display
                $start_page = max(1, $users_page - $half_range);
                $end_page = min($users_total_pages, $users_page + $half_range);

                // Adjust the range if we're near the start or end
                if ($users_page <= $half_range) {
                    $end_page = min($users_total_pages, $max_visible_pages);
                }
                if ($users_page > $users_total_pages - $half_range) {
                    $start_page = max(1, $users_total_pages - $max_visible_pages + 1);
                }
                ?>

                <!-- Previous Link -->
                <?php if ($users_page > 1) : ?>
                <a href="#" onclick="fetchUsersPage(<?= $users_page - 1 ?>); return false;">« Previous</a>
                <?php else : ?>
                <span class="disabled">« Previous</span>
                <?php endif; ?>

                <!-- First Page -->
                <?php if ($start_page > 1) : ?>
                <a href="#" onclick="fetchUsersPage(1); return false;">1</a>
                <?php if ($start_page > 2) : ?>
                <span class="ellipsis">...</span>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Page Numbers in Range -->
                <?php for ($i = $start_page; $i <= $end_page; $i++) : ?>
                <?php if ($i == $users_page) : ?>
                <span class="current"><?= $i ?></span>
                <?php else : ?>
                <a href="#" onclick="fetchUsersPage(<?= $i ?>); return false;"><?= $i ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <!-- Last Page -->
                <?php if ($end_page < $users_total_pages) : ?>
                <?php if ($end_page < $users_total_pages - 1) : ?>
                <span class="ellipsis">...</span>
                <?php endif; ?>
                <a href="#"
                    onclick="fetchUsersPage(<?= $users_total_pages ?>); return false;"><?= $users_total_pages ?></a>
                <?php endif; ?>

                <!-- Next Link -->
                <?php if ($users_page < $users_total_pages) : ?>
                <a href="#" onclick="fetchUsersPage(<?= $users_page + 1 ?>); return false;">Next »</a>
                <?php else : ?>
                <span class="disabled">Next »</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Audit Log Tab Content -->
    <div class="tab-content" id="audit-tab">
        <div class="audit-table">
            <h3 style="color: #34502b;">Audit Log</h3>
            <div class="search-container">
                <input type="text" id="searchAuditUserID" placeholder="Admin ID">
                <input type="text" id="searchAction" placeholder="Action">
                <button class="search-btn" onclick="searchAudit()">SEARCH</button>
                <button class="clear-btn" onclick="clearAuditSearch()">CLEAR</button>
            </div>
            <div class="table-controls">
                <select id="auditOrderBy" class="custom-select" onchange="applyAuditFilters()">
                    <option value="timestamp|DESC" data-icon="fa-solid fa-arrow-down-long">Timestamp (Newest)</option>
                    <option value="timestamp|ASC" data-icon="fa-solid fa-arrow-up-long">Timestamp (Oldest)</option>
                    <option value="log_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">Log ID (Descending)</option>
                    <option value="log_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">Log ID (Ascending)</option>
                    <option value="admin_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">Admin ID (Low to High)</option>
                    <option value="admin_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">Admin ID (High to Low)</option>
                </select>
                <select id="auditRowsPerPage" class="rows-per-page-select" onchange="updateAuditRowsPerPage()">
                    <option value="5" <?= $audit_rows_per_page == 5 ? 'selected' : '' ?>>5 pages</option>
                    <option value="10" <?= $audit_rows_per_page == 10 ? 'selected' : '' ?>>10 pages</option>
                    <option value="25" <?= $audit_rows_per_page == 25 ? 'selected' : '' ?>>25 pages</option>
                    <option value="50" <?= $audit_rows_per_page == 50 ? 'selected' : '' ?>>50 pages</option>
                </select>
            </div>
            <div class="table-responsive rounded-3" id="audit-table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Admin ID</th>
                            <th>Username</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($auditResult->num_rows > 0) : ?>
                        <?php while ($row = $auditResult->fetch_assoc()) : ?>
                        <tr>
                            <td><?= $row['log_id'] ?></td>
                            <td><?= $row['admin_id'] ?? '-' ?></td>
                            <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($row['action']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= $row['timestamp'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else : ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #666;">No audit logs found.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Audit Pagination -->
            <div class="pagination" id="audit-pagination">
                <?php
                $audit_max_visible_pages = 5; // Show 5 page numbers around the current page
                $audit_half_range = floor($audit_max_visible_pages / 2);

                // Calculate the range of pages to display
                $audit_start_page = max(1, $audit_page - $audit_half_range);
                $audit_end_page = min($audit_total_pages, $audit_page + $audit_half_range);

                // Adjust the range if we're near the start or end
                if ($audit_page <= $audit_half_range) {
                    $audit_end_page = min($audit_total_pages, $audit_max_visible_pages);
                }
                if ($audit_page > $audit_total_pages - $audit_half_range) {
                    $audit_start_page = max(1, $audit_total_pages - $audit_max_visible_pages + 1);
                }
                ?>

                <!-- Previous Link -->
                <?php if ($audit_page > 1) : ?>
                <a href="#" onclick="fetchAuditPage(<?= $audit_page - 1 ?>); return false;">« Previous</a>
                <?php else : ?>
                <span class="disabled">« Previous</span>
                <?php endif; ?>

                <!-- First Page -->
                <?php if ($audit_start_page > 1) : ?>
                <a href="#" onclick="fetchAuditPage(1); return false;">1</a>
                <?php if ($audit_start_page > 2) : ?>
                <span class="ellipsis">...</span>
                <?php endif; ?>
                <?php endif; ?>

                <!-- Page Numbers in Range -->
                <?php for ($i = $audit_start_page; $i <= $audit_end_page; $i++) : ?>
                <?php if ($i == $audit_page) : ?>
                <span class="current"><?= $i ?></span>
                <?php else : ?>
                <a href="#" onclick="fetchAuditPage(<?= $i ?>); return false;"><?= $i ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <!-- Last Page -->
                <?php if ($audit_end_page < $audit_total_pages) : ?>
                <?php if ($audit_end_page < $audit_total_pages - 1) : ?>
                <span class="ellipsis">...</span>
                <?php endif; ?>
                <a href="#"
                    onclick="fetchAuditPage(<?= $audit_total_pages ?>); return false;"><?= $audit_total_pages ?></a>
                <?php endif; ?>

                <!-- Next Link -->
                <?php if ($audit_page < $audit_total_pages) : ?>
                <a href="#" onclick="fetchAuditPage(<?= $audit_page + 1 ?>); return false;">Next »</a>
                <?php else : ?>
                <span class="disabled">Next »</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../handlers/adduser_handler.php" method="POST">
                    <div class="modal-body">
                        <label>First Name:</label>
                        <input type="text" name="first_name" class="form-control" required>
                        <label>Middle Name (Optional):</label>
                        <input type="text" name="middle_name" class="form-control">
                        <label>Last Name:</label>
                        <input type="text" name="last_name" class="form-control" required>
                        <label>Username:</label>
                        <input type="text" name="username" class="form-control" required>
                        <label>Password:</label>
                        <input type="text" name="password" class="form-control" required>
                        <label>Email:</label>
                        <input type="email" name="email" class="form-control" required>
                        <label>Phone Number:</label>
                        <input type="text" name="phonenumber" class="form-control" required>
                        <label>Role:</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn" style="background-color: #34502b; color: white;">Add</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Session Messages -->
    <?php if (isset($_SESSION['success'])) : ?>
    <div class="alert alert-success alert-dismissible fade show floating-alert text-center" role="alert">
        <div class="alert-content">
            <?php
                $full_message = $_SESSION['success'];
                $short_message = strlen($full_message) > 100 ? substr($full_message, 0, 100) . '...' : $full_message;
                ?>
            <span class="alert-short"><?= htmlspecialchars($short_message) ?></span>
            <span class="alert-full d-none"><?= htmlspecialchars($full_message) ?></span>
            <?php if (strlen($full_message) > 100) : ?>
            <button type="button" class="btn btn-link btn-sm toggle-message">Show More</button>
            <?php endif; ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])) : ?>
    <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#userOrderBy').select2({
        templateResult: formatOption,
        templateSelection: formatSelection,
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth: true,
        width: '50px'
    });
    $('#auditOrderBy').select2({
        templateResult: formatOption,
        templateSelection: formatSelection,
        minimumResultsForSearch: Infinity,
        dropdownAutoWidth: true,
        width: '50px'
    });

    const urlParams = new URLSearchParams(window.location.search);
    $('#searchUserID').val(urlParams.get('user_id') || '');
    $('#searchUsername').val(urlParams.get('username') || '');
    $('#searchRole').val(urlParams.get('role') || '');
    $('#searchStatus').val(urlParams.get('status') || '');
    $('#userOrderBy').val(urlParams.get('order_by') || 'admin_id|DESC');
    $('#usersRowsPerPage').val(urlParams.get('users_rows') || '5');
    $('#searchAuditUserID').val(urlParams.get('audit_user_id') || '');
    $('#searchAction').val(urlParams.get('action') || '');
    $('#auditOrderBy').val(urlParams.get('audit_order_by') || 'timestamp|DESC');
    $('#auditRowsPerPage').val(urlParams.get('audit_rows') || '5');

    $('.tabs span').on('click', function() {
        $('.tabs span').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        const tabId = $(this).data('tab') + '-tab';
        $('#' + tabId).addClass('active');
    });

    updatePaginationLinks('users');
    updatePaginationLinks('audit');
});

function formatOption(option) {
    if (!option.element) return option.text;
    return $('<span><i class="' + $(option.element).data('icon') + ' me-2"></i>' + option.text + '</span>');
}

function formatSelection(option) {
    if (!option.element) return option.text;
    return $('<span><i class="' + $(option.element).data('icon') + '"></i></span>');
}

function buildUrl(base, params) {
    return params.length > 0 ? `${base}&${params.join('&')}` : base;
}

function searchUsers() {
    const userID = $('#searchUserID').val().trim();
    const username = $('#searchUsername').val().trim();
    const role = $('#searchRole').val();
    const status = $('#searchStatus').val();
    const orderBy = $('#userOrderBy').val();
    const rowsPerPage = $('#usersRowsPerPage').val();

    let url = '../../resource/layout/web-layout.php?page=account&search=1&users_page=1';
    const params = [];
    if (userID) params.push(`user_id=${encodeURIComponent(userID)}`);
    if (username) params.push(`username=${encodeURIComponent(username)}`);
    if (role) params.push(`role=${encodeURIComponent(role)}`);
    if (status) params.push(`status=${encodeURIComponent(status)}`);
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
    if (rowsPerPage) params.push(`users_rows=${encodeURIComponent(rowsPerPage)}`);
    appendAuditParams(params);

    window.location.href = buildUrl(url, params);
}

function applyUserFilters() {
    const userID = $('#searchUserID').val().trim();
    const username = $('#searchUsername').val().trim();
    const role = $('#searchRole').val();
    const status = $('#searchStatus').val();
    const orderBy = $('#userOrderBy').val();
    const rowsPerPage = $('#usersRowsPerPage').val();

    let url = '../../resource/layout/web-layout.php?page=account&users_page=1';
    const params = [];
    if (userID || username || role || status) params.push('search=1');
    if (userID) params.push(`user_id=${encodeURIComponent(userID)}`);
    if (username) params.push(`username=${encodeURIComponent(username)}`);
    if (role) params.push(`role=${encodeURIComponent(role)}`);
    if (status) params.push(`status=${encodeURIComponent(status)}`);
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
    if (rowsPerPage) params.push(`users_rows=${encodeURIComponent(rowsPerPage)}`);
    appendAuditParams(params);

    window.location.href = buildUrl(url, params);
}

function clearUserSearch() {
    $('#searchUserID').val('');
    $('#searchUsername').val('');
    $('#searchRole').val('');
    $('#searchStatus').val('');
    const orderBy = $('#userOrderBy').val();
    const rowsPerPage = $('#usersRowsPerPage').val();

    let url = '../../resource/layout/web-layout.php?page=account&users_page=1';
    const params = [];
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
    if (rowsPerPage) params.push(`users_rows=${encodeURIComponent(rowsPerPage)}`);
    appendAuditParams(params);

    window.location.href = buildUrl(url, params);
}

function searchAudit() {
    const auditUserID = $('#searchAuditUserID').val().trim();
    const action = $('#searchAction').val().trim();
    const auditOrderBy = $('#auditOrderBy').val();
    const rowsPerPage = $('#auditRowsPerPage').val();

    let url = '../../resource/layout/web-layout.php?page=account&audit_search=1&audit_page=1';
    const params = [];
    if (auditUserID) params.push(`audit_user_id=${encodeURIComponent(auditUserID)}`);
    if (action) params.push(`action=${encodeURIComponent(action)}`);
    if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
    if (rowsPerPage) params.push(`audit_rows=${encodeURIComponent(rowsPerPage)}`);
    appendUserParams(params);

    window.location.href = buildUrl(url, params);
}

function applyAuditFilters() {
    const auditUserID = $('#searchAuditUserID').val().trim();
    const action = $('#searchAction').val().trim();
    const auditOrderBy = $('#auditOrderBy').val();
    const rowsPerPage = $('#auditRowsPerPage').val();

    let url = '../../resource/layout/web-layout.php?page=account&audit_page=1';
    const params = [];
    if (auditUserID || action) params.push('audit_search=1');
    if (auditUserID) params.push(`audit_user_id=${encodeURIComponent(auditUserID)}`);
    if (action) params.push(`action=${encodeURIComponent(action)}`);
    if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
    if (rowsPerPage) params.push(`audit_rows=${encodeURIComponent(rowsPerPage)}`);
    appendUserParams(params);

    window.location.href = buildUrl(url, params);
}

function clearAuditSearch() {
    $('#searchAuditUserID').val('');
    $('#searchAction').val('');
    const auditOrderBy = $('#auditOrderBy').val();
    const rowsPerPage = $('#auditRowsPerPage').val();

    let url = '../../resource/layout/web-layout.php?page=account&audit_page=1';
    const params = [];
    if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
    if (rowsPerPage) params.push(`audit_rows=${encodeURIComponent(rowsPerPage)}`);
    appendUserParams(params);

    window.location.href = buildUrl(url, params);
}

function appendUserParams(params) {
    const userID = $('#searchUserID').val().trim();
    const username = $('#searchUsername').val().trim();
    const role = $('#searchRole').val();
    const status = $('#searchStatus').val();
    const orderBy = $('#userOrderBy').val();
    const rowsPerPage = $('#usersRowsPerPage').val();
    if (userID || username || role || status) params.push('search=1');
    if (userID) params.push(`user_id=${encodeURIComponent(userID)}`);
    if (username) params.push(`username=${encodeURIComponent(username)}`);
    if (role) params.push(`role=${encodeURIComponent(role)}`);
    if (status) params.push(`status=${encodeURIComponent(status)}`);
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
    if (rowsPerPage) params.push(`users_rows=${encodeURIComponent(rowsPerPage)}`);
}

function appendAuditParams(params) {
    const auditUserID = $('#searchAuditUserID').val().trim();
    const action = $('#searchAction').val().trim();
    const auditOrderBy = $('#auditOrderBy').val();
    const rowsPerPage = $('#auditRowsPerPage').val();
    if (auditUserID || action) params.push('audit_search=1');
    if (auditUserID) params.push(`audit_user_id=${encodeURIComponent(auditUserID)}`);
    if (action) params.push(`action=${encodeURIComponent(action)}`);
    if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
    if (rowsPerPage) params.push(`audit_rows=${encodeURIComponent(rowsPerPage)}`);
}

function fetchUsersPage(page) {
    const userID = $('#searchUserID').val().trim();
    const username = $('#searchUsername').val().trim();
    const role = $('#searchRole').val();
    const status = $('#searchStatus').val();
    const orderBy = $('#userOrderBy').val();
    const rowsPerPage = $('#usersRowsPerPage').val();

    let url = `../../resource/layout/web-layout.php?page=account&users_page=${page}`;
    const params = [];
    if (userID || username || role || status) params.push('search=1');
    if (userID) params.push(`user_id=${encodeURIComponent(userID)}`);
    if (username) params.push(`username=${encodeURIComponent(username)}`);
    if (role) params.push(`role=${encodeURIComponent(role)}`);
    if (status) params.push(`status=${encodeURIComponent(status)}`);
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
    if (rowsPerPage) params.push(`users_rows=${encodeURIComponent(rowsPerPage)}`);
    appendAuditParams(params);

    const xhr = new XMLHttpRequest();
    xhr.open("GET", buildUrl(url, params), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(xhr.responseText, 'text/html');
            const newTable = doc.getElementById('users-table-container');
            const newPagination = doc.getElementById('users-pagination');

            document.getElementById('users-table-container').innerHTML = newTable.innerHTML;
            document.getElementById('users-pagination').innerHTML = newPagination.innerHTML;

            updatePaginationLinks('users');
        }
    };
    xhr.send();
}

function fetchAuditPage(page) {
    const auditUserID = $('#searchAuditUserID').val().trim();
    const action = $('#searchAction').val().trim();
    const auditOrderBy = $('#auditOrderBy').val();
    const rowsPerPage = $('#auditRowsPerPage').val();

    let url = `../../resource/layout/web-layout.php?page=account&audit_page=${page}`;
    const params = [];
    if (auditUserID || action) params.push('audit_search=1');
    if (auditUserID) params.push(`audit_user_id=${encodeURIComponent(auditUserID)}`);
    if (action) params.push(`action=${encodeURIComponent(action)}`);
    if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
    if (rowsPerPage) params.push(`audit_rows=${encodeURIComponent(rowsPerPage)}`);
    appendUserParams(params);

    const xhr = new XMLHttpRequest();
    xhr.open("GET", buildUrl(url, params), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(xhr.responseText, 'text/html');
            const newTable = doc.getElementById('audit-table-container');
            const newPagination = doc.getElementById('audit-pagination');

            document.getElementById('audit-table-container').innerHTML = newTable.innerHTML;
            document.getElementById('audit-pagination').innerHTML = newPagination.innerHTML;

            updatePaginationLinks('audit');
        }
    };
    xhr.send();
}

function updateUsersRowsPerPage() {
    const userID = $('#searchUserID').val().trim();
    const username = $('#searchUsername').val().trim();
    const role = $('#searchRole').val();
    const status = $('#searchStatus').val();
    const orderBy = $('#userOrderBy').val();
    const rowsPerPage = $('#usersRowsPerPage').val();

    let url = `../../resource/layout/web-layout.php?page=account&users_page=1`;
    const params = [];
    if (userID || username || role || status) params.push('search=1');
    if (userID) params.push(`user_id=${encodeURIComponent(userID)}`);
    if (username) params.push(`username=${encodeURIComponent(username)}`);
    if (role) params.push(`role=${encodeURIComponent(role)}`);
    if (status) params.push(`status=${encodeURIComponent(status)}`);
    if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
    if (rowsPerPage) params.push(`users_rows=${encodeURIComponent(rowsPerPage)}`);
    appendAuditParams(params);

    window.location.href = buildUrl(url, params);
}

function updateAuditRowsPerPage() {
    const auditUserID = $('#searchAuditUserID').val().trim();
    const action = $('#searchAction').val().trim();
    const auditOrderBy = $('#auditOrderBy').val();
    const rowsPerPage = $('#auditRowsPerPage').val();

    let url = `../../resource/layout/web-layout.php?page=account&audit_page=1`;
    const params = [];
    if (auditUserID || action) params.push('audit_search=1');
    if (auditUserID) params.push(`audit_user_id=${encodeURIComponent(auditUserID)}`);
    if (action) params.push(`action=${encodeURIComponent(action)}`);
    if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
    if (rowsPerPage) params.push(`audit_rows=${encodeURIComponent(rowsPerPage)}`);
    appendUserParams(params);

    window.location.href = buildUrl(url, params);
}

function toggleUserStatus(adminId, newStatus) {
    const action = newStatus === 'inactive' ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} this admin?`)) {
        window.location.href = "../../handlers/deactivateuser_handler.php?id=" + adminId + "&status=" + newStatus;
    }
}

function updatePaginationLinks(type) {
    const paginationLinks = document.querySelectorAll(`#${type}-pagination a`);
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const pageText = this.textContent.trim();
            let page;
            if (pageText === '« Previous') {
                page = type === 'users' ? <?= $users_page ?> - 1 : <?= $audit_page ?> - 1;
            } else if (pageText === 'Next »') {
                page = type === 'users' ? <?= $users_page ?> + 1 : <?= $audit_page ?> + 1;
            } else {
                page = parseInt(pageText);
            }
            if (type === 'users') {
                fetchUsersPage(page);
            } else {
                fetchAuditPage(page);
            }
        });
    });
}

setTimeout(() => {
    const alerts = document.querySelectorAll(".floating-alert");
    alerts.forEach(alert => {
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    });
}, 20000);

document.querySelectorAll('.toggle-message').forEach(button => {
    button.addEventListener('click', () => {
        const alert = button.closest('.alert');
        const short = alert.querySelector('.alert-short');
        const full = alert.querySelector('.alert-full');
        if (short.classList.contains('d-none')) {
            short.classList.remove('d-none');
            full.classList.add('d-none');
            button.textContent = 'Show More';
        } else {
            short.classList.add('d-none');
            full.classList.remove('d-none');
            button.textContent = 'Show Less';
        }
    });
});
</script>