<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="dashboard-sidebar" id="dashboardSidebar">

    <!-- Brand -->
    <a href="/client/dashboard.php" class="sidebar-brand text-decoration-none">

        <div class="brand-mark">T</div>

        <span class="fw-bold">TaskFlow</span>

        <button type="button" class="sidebar-close" id="sidebarClose">
            &times;
        </button>

    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <span class="sidebar-section-label">Workspace</span>

        <a href="/client/dashboard.php"
            class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <span class="nav-icon">⌂</span>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="/client/projects.php"
            class="<?php echo ($currentPage === 'projects.php') ? 'active' : ''; ?>">
            <span class="nav-icon">▣</span>
            <span class="nav-label">My Projects</span>
        </a>

        <a href="/client/create-project.php"
            class="<?php echo ($currentPage === 'create-project.php') ? 'active' : ''; ?>">
            <span class="nav-icon">＋</span>
            <span class="nav-label">Create Project</span>
        </a>

        <span class="sidebar-section-label">Management</span>

        <a href="/client/freelancers.php"
            class="<?php echo ($currentPage === 'freelancers.php') ? 'active' : ''; ?>">
            <span class="nav-icon">♙</span>
            <span class="nav-label">Find Freelancers</span>
        </a>

        <a href="/client/submissions.php"
            class="<?php echo ($currentPage === 'submissions.php') ? 'active' : ''; ?>">
            <span class="nav-icon">▤</span>
            <span class="nav-label">Submissions</span>
        </a>

        <span class="sidebar-section-label">Account</span>

        <a href="/client/profile.php"
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