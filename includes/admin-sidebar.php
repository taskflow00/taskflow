<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="dashboard-sidebar" id="dashboardSidebar">

    <!-- Brand -->
    <a href="/admin/dashboard.php" class="sidebar-brand text-decoration-none">

        <div class="brand-mark">T</div>

        <span class="fw-bold">TaskFlow</span>

        <button type="button" class="sidebar-close" id="sidebarClose">
            &times;
        </button>

    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <span class="sidebar-section-label">Overview</span>

        <a href="/admin/dashboard.php"
            class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <span class="nav-icon">⌂</span>
            <span class="nav-label">Dashboard</span>
        </a>

        <span class="sidebar-section-label">Management</span>

        <a href="/admin/users.php"
            class="<?php echo ($currentPage === 'users.php') ? 'active' : ''; ?>">
            <span class="nav-icon">♙</span>
            <span class="nav-label">User Management</span>
        </a>

        <a href="/admin/projects.php"
            class="<?php echo ($currentPage === 'projects.php') ? 'active' : ''; ?>">
            <span class="nav-icon">▣</span>
            <span class="nav-label">Project Management</span>
        </a>

        <span class="sidebar-section-label">Account</span>

        <a href="/admin/profile.php"
            class="<?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">
            <span class="nav-icon">⚙</span>
            <span class="nav-label">My Profile</span>
        </a>

        <a href="/logout.php">
            <span class="nav-icon">↪</span>
            <span class="nav-label">Logout</span>
        </a>

    </nav>

</aside>