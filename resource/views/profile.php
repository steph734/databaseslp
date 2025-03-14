<div class="dropdown">
    <a href="#" class="user-icon" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="User Menu">
        <i class="fa-solid fa-user"></i>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
        <li><a class="dropdown-item" href="../../resource/layout/web-layout.php?page=profileadmin">Profile</a></li>
        <li><a class="dropdown-item" href="../../resource/layout/web-layout.php?page=account">Accounts</a></li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item text-danger" href="../../resource/logout.php">Logout</a></li>
    </ul>
</div>

<style>
    .user-icon {
        font-size: 20px;
        color: #34502b;
        /* Match account.php primary color */
        text-decoration: none;
        margin-left: 20px;
        display: flex;
        align-items: center;
        transition: color 0.3s ease;
    }

    .user-icon:hover {
        color: #4a6f3e;
        /* Slightly lighter shade for hover */
    }

    .dropdown-menu {
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        /* Match account.php shadow */
        min-width: 180px;
        /* Ensure enough space for username */
    }

    .dropdown-item {
        padding: 8px 15px;
        color: #333;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dropdown-item:hover {
        background-color: #f5f5f5;
        /* Light gray hover */
        color: #34502b;
    }

    .dropdown-item.text-danger {
        color: #dc3545;
        /* Bootstrap danger color */
    }

    .dropdown-item.text-danger:hover {
        background-color: #fce6e8;
        /* Light red hover */
        color: #c82333;
    }

    .dropdown-divider {
        margin: 5px 0;
        border-color: #ddd;
        /* Subtle divider */
    }

    @media (max-width: 768px) {
        .user-icon {
            margin-left: 10px;
            /* Reduce margin on smaller screens */
        }
    }
</style>