<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="./../../pages/user/user_dashboard.php">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="./../../pages/user/user_contacts.php">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Contacts</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
                <i class="icon-columns menu-icon"></i>
                <span class="menu-title">Profile</span>
                <i class="menu-arrow"></i>
            </a>

            <div class="collapse" id="form-elements">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="./../../pages/user/update_profile.php">Profile Details</a></li>
                </ul>

                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="./../../pages/user/change_password.php">Change Password</a></li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="./../../pages/user/user_settings.php">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Roles & Permissions</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="./../../pages/user/view_activity_logs.php">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Activity Logs</span>
            </a>
        </li>
    </ul>
</nav>