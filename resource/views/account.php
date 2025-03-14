<?php
include '../../database/database.php';

// Users Query (adapted for admin table)
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
}

if (isset($_GET['order_by']) && !empty($_GET['order_by'])) {
    list($column, $direction) = explode('|', $_GET['order_by']);
    $column = $conn->real_escape_string($column);
    $direction = strtoupper($conn->real_escape_string($direction));
    if (in_array($column, ['admin_id', 'username', 'role']) && in_array($direction, ['ASC', 'DESC'])) {
        $orderClause = "ORDER BY a.$column $direction";
    }
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

$usersQuery = "SELECT a.admin_id, a.username, a.role, a.first_name, a.last_name, a.email, a.phonenumber 
               FROM admin a 
               $whereClause 
               $orderClause";
$usersResult = $conn->query($usersQuery);

if (!$usersResult) {
    echo "Users Query failed: " . $conn->error;
    exit;
}

// Audit Log Query
$auditWhereConditions = [];
$auditOrderClause = "ORDER BY a.timestamp DESC"; // Default ordering

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
               $auditOrderClause";
$auditResult = $conn->query($auditQuery);

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
        /* Assuming a sidebar exists in your layout */
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
        justify-content: flex-start;
        margin-bottom: 10px;
        gap: 10px;
        align-items: center;
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

    .custom-select:focus {
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
    }

    @media (max-width: 500px) {
        .main-content {
            margin-left: 0;
            width: 100%;
        }
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
                <button class="search-btn" onclick="searchUsers()">SEARCH</button>
                <button class="clear-btn" onclick="clearUserSearch()">CLEAR</button>
            </div>

            <div class="table-controls">
                <button class="create-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fa-solid fa-user-plus"></i> ADD ADMIN
                </button>
                <select id="userOrderBy" class="custom-select" onchange="applyUserFilters()">
                    <option value="admin_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">ID (Descending)</option>
                    <option value="admin_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">ID (Ascending)</option>
                    <option value="username|ASC" data-icon="fa-solid fa-arrow-up-a-z">Username (A-Z)</option>
                    <option value="username|DESC" data-icon="fa-solid fa-arrow-down-z-a">Username (Z-A)</option>
                </select>
            </div>

            <div class="table-responsive rounded-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Role</th>
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
                                    <td>
                                        <button class="btn btn-sm text-warning"
                                            onclick='loadEditUserModal(<?= json_encode($row) ?>)' data-bs-toggle="modal"
                                            data-bs-target="#editUserModal">
                                            <i class="fa fa-edit" style="color: #ffc107;"></i>
                                        </button>
                                        <button class="btn btn-sm text-danger"
                                            onclick="confirmDeleteUser(<?= $row['admin_id'] ?>)">
                                            <i class="fa fa-trash" style="color: #dc3545;"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px; color: #666;">No admin records
                                    found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                    <option value="timestamp|DESC" data-icon="fa-solid fa-arrow-down-long">Timestamp (Newest)
                    </option>
                    <option value="timestamp|ASC" data-icon="fa-solid fa-arrow-up-long">Timestamp (Oldest)</option>
                    <option value="log_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">Log ID (Descending)</option>
                    <option value="log_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">Log ID (Ascending)</option>
                    <option value="admin_id|ASC" data-icon="fa-solid fa-arrow-up-1-9">Admin ID (Low to High)
                    </option>
                    <option value="admin_id|DESC" data-icon="fa-solid fa-arrow-down-9-1">Admin ID (High to Low)
                    </option>
                </select>
            </div>

            <div class="table-responsive rounded-3">
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
                                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">No audit logs
                                    found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                        <input type="password" name="password" class="form-control" required>
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

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../handlers/edituser_handler.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="admin_id">
                        <label>First Name:</label>
                        <input type="text" name="first_name" class="form-control" required>
                        <label>Middle Name (Optional):</label>
                        <input type="text" name="middle_name" class="form-control">
                        <label>Last Name:</label>
                        <input type="text" name="last_name" class="form-control" required>
                        <label>Username:</label>
                        <input type="text" name="username" class="form-control" required>
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
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
        // Initialize Select2 for Users
        $('#userOrderBy').select2({
            templateResult: formatOption,
            templateSelection: formatSelection,
            minimumResultsForSearch: Infinity,
            dropdownAutoWidth: true,
            width: '50px'
        });

        // Initialize Select2 for Audit Log
        $('#auditOrderBy').select2({
            templateResult: formatOption,
            templateSelection: formatSelection,
            minimumResultsForSearch: Infinity,
            dropdownAutoWidth: true,
            width: '50px'
        });

        $('#userOrderBy').on('change', applyUserFilters);
        $('#auditOrderBy').on('change', applyAuditFilters);

        const urlParams = new URLSearchParams(window.location.search);
        $('#searchUserID').val(urlParams.get('user_id') || '');
        $('#searchUsername').val(urlParams.get('username') || '');
        $('#searchRole').val(urlParams.get('role') || '');
        $('#userOrderBy').val(urlParams.get('order_by') || 'admin_id|DESC');
        $('#searchAuditUserID').val(urlParams.get('audit_user_id') || '');
        $('#searchAction').val(urlParams.get('action') || '');
        $('#auditOrderBy').val(urlParams.get('audit_order_by') || 'timestamp|DESC');

        // Tab switching
        $('.tabs span').on('click', function() {
            $('.tabs span').removeClass('active');
            $(this).addClass('active');

            $('.tab-content').removeClass('active');
            const tabId = $(this).data('tab') + '-tab';
            $('#' + tabId).addClass('active');
        });
    });

    function formatOption(option) {
        if (!option.element) return option.text;
        return $('<span><i class="' + $(option.element).data('icon') + ' me-2"></i>' + option.text + '</span>');
    }

    function formatSelection(option) {
        if (!option.element) return option.text;
        return $('<span><i class="' + $(option.element).data('icon') + '"></i></span>');
    }

    // Users Search
    function searchUsers() {
        const userID = $('#searchUserID').val().trim();
        const username = $('#searchUsername').val().trim();
        const role = $('#searchRole').val();
        const orderBy = $('#userOrderBy').val();

        let url = '../../resource/layout/web-layout.php?page=account&search=1';
        const params = [];
        if (userID) params.push(`user_id=${encodeURIComponent(userID)}`);
        if (username) params.push(`username=${encodeURIComponent(username)}`);
        if (role) params.push(`role=${encodeURIComponent(role)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
        appendAuditParams(params);

        if (params.length > 0) url += '&' + params.join('&');
        window.location.href = url;
    }

    // Users Table Filters
    function applyUserFilters() {
        const userID = $('#searchUserID').val().trim();
        const username = $('#searchUsername').val().trim();
        const role = $('#searchRole').val();
        const orderBy = $('#userOrderBy').val();

        let url = '../../resource/layout/web-layout.php?page=account';
        const params = [];
        if (userID || username || role) params.push('search=1');
        if (userID) params.push(`user_id=${encodeURIComponent(userID)}`);
        if (username) params.push(`username=${encodeURIComponent(username)}`);
        if (role) params.push(`role=${encodeURIComponent(role)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
        appendAuditParams(params);

        if (params.length > 0) url += '&' + params.join('&');
        window.location.href = url;
    }

    // Clear Users Search
    function clearUserSearch() {
        $('#searchUserID').val('');
        $('#searchUsername').val('');
        $('#searchRole').val('');
        const orderBy = $('#userOrderBy').val();

        let url = '../../resource/layout/web-layout.php?page=account';
        const params = [];
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
        appendAuditParams(params);

        if (params.length > 0) url += '&' + params.join('&');
        window.location.href = url;
    }

    // Audit Search
    function searchAudit() {
        const auditUserID = $('#searchAuditUserID').val().trim();
        const action = $('#searchAction').val().trim();
        const auditOrderBy = $('#auditOrderBy').val();

        let url = '../../resource/layout/web-layout.php?page=account&audit_search=1';
        const params = [];
        if (auditUserID) params.push(`audit_user_id=${encodeURIComponent(auditUserID)}`);
        if (action) params.push(`action=${encodeURIComponent(action)}`);
        if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
        appendUserParams(params);

        if (params.length > 0) url += '&' + params.join('&');
        window.location.href = url;
    }

    // Audit Table Filters
    function applyAuditFilters() {
        const auditUserID = $('#searchAuditUserID').val().trim();
        const action = $('#searchAction').val().trim();
        const auditOrderBy = $('#auditOrderBy').val();

        let url = '../../resource/layout/web-layout.php?page=account';
        const params = [];
        if (auditUserID || action) params.push('audit_search=1');
        if (auditUserID) params.push(`audit_user_id=${encodeURIComponent(auditUserID)}`);
        if (action) params.push(`action=${encodeURIComponent(action)}`);
        if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
        appendUserParams(params);

        if (params.length > 0) url += '&' + params.join('&');
        window.location.href = url;
    }

    // Clear Audit Search
    function clearAuditSearch() {
        $('#searchAuditUserID').val('');
        $('#searchAction').val('');
        const auditOrderBy = $('#auditOrderBy').val();

        let url = '../../resource/layout/web-layout.php?page=account';
        const params = [];
        if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
        appendUserParams(params);

        if (params.length > 0) url += '&' + params.join('&');
        window.location.href = url;
    }

    // Helper functions to append parameters
    function appendUserParams(params) {
        const userID = $('#searchUserID').val().trim();
        const username = $('#searchUsername').val().trim();
        const role = $('#searchRole').val();
        const orderBy = $('#userOrderBy').val();
        if (userID || username || role) params.push('search=1');
        if (userID) params.push(`user_id=${encodeURIComponent(userID)}`);
        if (username) params.push(`username=${encodeURIComponent(username)}`);
        if (role) params.push(`role=${encodeURIComponent(role)}`);
        if (orderBy) params.push(`order_by=${encodeURIComponent(orderBy)}`);
    }

    function appendAuditParams(params) {
        const auditUserID = $('#searchAuditUserID').val().trim();
        const action = $('#searchAction').val().trim();
        const auditOrderBy = $('#auditOrderBy').val();
        if (auditUserID || action) params.push('audit_search=1');
        if (auditUserID) params.push(`audit_user_id=${encodeURIComponent(auditUserID)}`);
        if (action) params.push(`action=${encodeURIComponent(action)}`);
        if (auditOrderBy) params.push(`audit_order_by=${encodeURIComponent(auditOrderBy)}`);
    }

    // Load Edit User Modal
    function loadEditUserModal(user) {
        document.querySelector("#editUserModal input[name='admin_id']").value = user.admin_id;
        document.querySelector("#editUserModal input[name='first_name']").value = user.first_name;
        document.querySelector("#editUserModal input[name='middle_name']").value = user.middle_name || '';
        document.querySelector("#editUserModal input[name='last_name']").value = user.last_name;
        document.querySelector("#editUserModal input[name='username']").value = user.username;
        document.querySelector("#editUserModal input[name='email']").value = user.email;
        document.querySelector("#editUserModal input[name='phonenumber']").value = user.phonenumber;
        document.querySelector("#editUserModal select[name='role']").value = user.role;
    }

    // Confirm Delete User
    function confirmDeleteUser(adminId) {
        if (confirm("Are you sure you want to delete this admin?")) {
            window.location.href = "../../handlers/deleteuser_handler.php?id=" + adminId;
        }
    }

    // Notification Handling
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