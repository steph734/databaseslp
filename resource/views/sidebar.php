<div class="sidebar">
    <!-- Sidebar Title -->

    <div class="sidebar-title" style="text-align: center;padding:20px; ">
        <h2><strong>INVENTORY SYSTEM</strong></h2>
    </div>


    <ul>
        <?php
        $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        $membership_active = ($current_page == 'membership' || $current_page == 'points') ? 'show' : '';
        $inventory_active = ($current_page == 'products' || $current_page == 'inventory' || $current_page == 'supplier') ? 'show' : '';
        $sales_active = ($current_page == 'sales' || $current_page == 'returns') ? 'show' : '';
        ?>

        <!-- Dashboard Section -->
        <li class="<?= ($current_page == 'dashboard') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=dashboard"><i class="fa fa-home"></i>&nbsp; Dashboard</a>
        </li>

        <!-- Customers & Membership Section -->
        <li class="<?= ($current_page == 'customer') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=customer"><i class="fa fa-user"></i>&nbsp; Customers</a>
        </li>
        <li class="nav-item">
            <a href="#membershipMenu" data-bs-toggle="collapse" class="nav-link">
                <i class="fa-solid fa-id-card"></i>&nbsp; Membership <i class="fa fa-caret-down"></i>
            </a>
            <ul id="membershipMenu" class="collapse list-unstyled <?= $membership_active ?>">
                <li class="<?= ($current_page == 'membership') ? 'active' : '' ?>">
                    <a href="../layout/web-layout.php?page=membership">&nbsp;Members</a>
                </li>
                <li class="<?= ($current_page == 'points') ? 'active' : '' ?>">
                    <a href="../layout/web-layout.php?page=points">&nbsp;Points</a>
                </li>
            </ul>
        </li>

        <!-- Sales & Transactions Section -->
        <li class="nav-item">
            <a href="#salesMenu" data-bs-toggle="collapse" class="nav-link">
                <i class="fa fa-shopping-cart"></i>&nbsp; Sales & Transactions <i class="fa fa-caret-down"></i>
            </a>
            <ul id="salesMenu" class="collapse list-unstyled <?= $sales_active ?>">
                <li class="<?= ($current_page == 'sales') ? 'active' : '' ?>">
                    <a href="../layout/web-layout.php?page=sales">&nbsp;Sales</a>
                </li>
                <li class="<?= ($current_page == 'returns') ? 'active' : '' ?>">
                    <a href="../layout/web-layout.php?page=returns">&nbsp;Returns</a>
                </li>
            </ul>
        </li>

        <!-- Inventory & Products Section -->
        <li class="nav-item">
            <a href="#inventoryMenu" data-bs-toggle="collapse" class="nav-link">
                <i class="fa fa-warehouse"></i>&nbsp; Inventory & Products <i class="fa fa-caret-down"></i>
            </a>
            <ul id="inventoryMenu" class="collapse list-unstyled <?= $inventory_active ?>">
                <li class="<?= ($current_page == 'products') ? 'active' : '' ?>">
                    <a href="../layout/web-layout.php?page=products">&nbsp;Products</a>
                </li>
                <li class="<?= ($current_page == 'inventory') ? 'active' : '' ?>">
                    <a href="../layout/web-layout.php?page=inventory">&nbsp;Inventory</a>
                </li>
                <li class="<?= ($current_page == 'supplier') ? 'active' : '' ?>">
                    <a href="../layout/web-layout.php?page=supplier">&nbsp;Suppliers</a>
                </li>
            </ul>
        </li>

        <!-- Reports Section -->
        <li class="<?= ($current_page == 'reports') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=reports"><i class="fa fa-chart-bar"></i>&nbsp; Reports</a>
        </li>
    </ul>

    <!-- Logout Section -->
    <ul class="logout">
        <li><a href="../login.php"><i class="fa fa-sign-out-alt"></i>&nbsp; Log Out</a></li>
    </ul>
</div>