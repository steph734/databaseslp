<div class="sidebar">
    <!-- Sidebar Title -->
    <div class="sidebar-title" style="text-align: center; padding: 20px;">
        <h2><strong>INVENTORY SYSTEM</strong></h2>
    </div>

    <ul>
        <?php
        $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        $membership_active = ($current_page == 'membership' || $current_page == 'points') ? 'show' : '';
        $inventory_active = ($current_page == 'products' || $current_page == 'inventory' || $current_page == 'supplier') ? 'show' : '';
        $sales_active = ($current_page == 'sales' || $current_page == 'returns') ? 'show' : '';
        ?>

        <?php if (in_array($current_page, ['account', 'profileadmin'])) : ?>
            <!-- Reduced sidebar -->
            <li class="<?= ($current_page == 'profileadmin') ? 'active' : '' ?>">
                <a href="../layout/web-layout.php?page=profileadmin"><i class="fa fa-user"></i>  Profile</a>
            </li>
            <li class="<?= ($current_page == 'account') ? 'active' : '' ?>">
                <a href="../layout/web-layout.php?page=account"><i class="fa fa-users"></i>  Accounts</a>
            </li>
            <li class="<?= ($current_page == 'returns') ? 'active' : '' ?>">
                <a href="../layout/web-layout.php?page=dashboard"><i class="fa-solid fa-door-open"></i></i>  Back</a>
            </li>
        <?php else : ?>
            <!-- Full sidebar for other pages -->
            <!-- Dashboard Section -->
            <li class="<?= ($current_page == 'dashboard') ? 'active' : '' ?>">
                <a href="../layout/web-layout.php?page=dashboard"><i class="fa fa-home"></i>  Dashboard</a>
            </li>

            <!-- Customers & Membership Section -->
            <li class="<?= ($current_page == 'customer') ? 'active' : '' ?>">
                <a href="../layout/web-layout.php?page=customer"><i class="fa fa-user"></i>  Customers</a>
            </li>
            <li class="nav-item">
                <a href="#membershipMenu" data-bs-toggle="collapse" class="nav-link">
                    <i class="fa-solid fa-id-card"></i>  Membership <i class="fa fa-caret-down"></i>
                </a>
                <ul id="membershipMenu" class="collapse list-unstyled <?= $membership_active ?>">
                    <li class="<?= ($current_page == 'membership') ? 'active' : '' ?>">
                        <a href="../layout/web-layout.php?page=membership"> Members</a>
                    </li>
                    <li class="<?= ($current_page == 'points') ? 'active' : '' ?>">
                        <a href="../layout/web-layout.php?page=points"> Points</a>
                    </li>
                </ul>
            </li>

            <!-- Sales & Transactions Section -->
            <li class="nav-item">
                <a href="#salesMenu" data-bs-toggle="collapse" class="nav-link">
                    <i class="fa fa-shopping-cart"></i>  Sales & Transactions <i class="fa fa-caret-down"></i>
                </a>
                <ul id="salesMenu" class="collapse list-unstyled <?= $sales_active ?>">
                    <li class="<?= ($current_page == 'sales') ? 'active' : '' ?>">
                        <a href="../layout/web-layout.php?page=sales"> Sales</a>
                    </li>
                    <li class="<?= ($current_page == 'returns') ? 'active' : '' ?>">
                        <a href="../layout/web-layout.php?page=returns"> Returns</a>
                    </li>
                </ul>
            </li>

            <!-- Inventory & Products Section -->
            <li class="nav-item">
                <a href="#inventoryMenu" data-bs-toggle="collapse" class="nav-link">
                    <i class="fa fa-warehouse"></i>  Inventory & Products <i class="fa fa-caret-down"></i>
                </a>
                <ul id="inventoryMenu" class="collapse list-unstyled <?= $inventory_active ?>">
                    <li class="<?= ($current_page == 'products') ? 'active' : '' ?>">
                        <a href="../layout/web-layout.php?page=products"> Products</a>
                    </li>
                    <li class="<?= ($current_page == 'inventory') ? 'active' : '' ?>">
                        <a href="../layout/web-layout.php?page=inventory"> Inventory</a>
                    </li>
                    <li class="<?= ($current_page == 'supplier') ? 'active' : '' ?>">
                        <a href="../layout/web-layout.php?page=supplier"> Suppliers</a>
                    </li>
                </ul>
            </li>

            <!-- Reports Section -->
            <li class="<?= ($current_page == 'reports') ? 'active' : '' ?>">
                <a href="../layout/web-layout.php?page=reports"><i class="fa fa-chart-bar"></i>  Reports</a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Logout Section (Always visible) -->
    <ul class="logout">
        <li><a href="../logout.php"><i class="fa fa-sign-out-alt"></i>  Log Out</a></li>
    </ul>
</div>