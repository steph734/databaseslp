<div class="sidebar">
    <div class="">

    </div>
    <ul>
        <?php
        $current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        ?>
        <li class="<?= ($current_page == 'dashboard') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=dashboard"><i class="fa fa-home"></i> Dashboard</a>
        </li>
        <li class="<?= ($current_page == 'customer') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=customer"><i class="fa fa-user"></i> Customers</a>
        </li>
        <li class="<?= ($current_page == 'membership') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=membership"><i class="fa-solid fa-id-card"></i> Membership</a>
        </li>
        <li class="<?= ($current_page == 'sales') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=sales"><i class="fa fa-shopping-cart"></i> Sales</a>
        </li>
        <li class="<?= ($current_page == 'products') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=products"><i class="fa fa-box"></i> Products</a>
        </li>
        <li class="<?= ($current_page == 'inventory') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=inventory"><i class="fa fa-warehouse"></i> Inventory</a>
        </li>
        <li class="<?= ($current_page == 'supplier') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=supplier"><i class="fa fa-truck"></i> Suppliers</a>
        </li>
        <li class="<?= ($current_page == 'returns') ? 'active' : '' ?>">
<<<<<<< HEAD
            <a href="../layout/web-layout.php?page=returns"><i class="fa fa-undo"></i> Returns</a>
=======
            <a href="../layout/web-layout.php?page=returns"><i class="fa-solid fa-right-left"></i> Returns</a>
>>>>>>> 2fbec378724fa20bea82d684e948bc4edecb67a8
        </li>
        <li class="<?= ($current_page == 'reports') ? 'active' : '' ?>">
            <a href="../layout/web-layout.php?page=reports"><i class="fa fa-chart-bar"></i> Reports</a>
        </li>
    </ul>
    <ul class="logout">
        <li><a href="../login.php"><i class="fa fa-sign-out-alt"></i> Log Out</a></li>
    </ul>
</div>