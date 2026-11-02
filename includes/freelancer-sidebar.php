<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="dashboard-sidebar" id="dashboardSidebar">

    <!-- Brand -->
    <a href="/freelancer/dashboard.php" class="sidebar-brand text-decoration-none">

        <div class="brand-mark">T</div>

        <span class="fw-bold">TaskFlow</span>

        <button type="button" class="sidebar-close" id="sidebarClose">
            &times;
        </button>

    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <span class="sidebar-section-label">Workspace</span>

        <a href="/freelancer/dashboard.php"
            class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <span class="nav-icon">⌂</span>
            <span class="nav-label">Dashboard</span>
        </a>

        <a href="/freelancer/invitations.php"
            class="<?php echo ($currentPage === 'invitations.php') ? 'active' : ''; ?>">
            <span class="nav-icon">✉</span>
            <span class="nav-label">Invitations</span>
        </a>

        <a href="/freelancer/projects.php"
            class="<?php echo ($currentPage === 'projects.php') ? 'active' : ''; ?>">
            <span class="nav-icon">▣</span>
            <span class="nav-label">My Projects</span>
        </a>

        <a href="/freelancer/tasks.php"
            class="<?php echo ($currentPage === 'tasks.php') ? 'active' : ''; ?>">
            <span class="nav-icon">☑</span>
            <span class="nav-label">My Tasks</span>
        </a>

        <span class="sidebar-section-label">Work Management</span>

        <a href="/freelancer/time-tracking.php"
            class="<?php echo ($currentPage === 'time-tracking.php') ? 'active' : ''; ?>">
            <span class="nav-icon">◷</span>
            <span class="nav-label">Time Tracking</span>
        </a>

        <a href="/freelancer/submissions.php"
            class="<?php echo ($currentPage === 'submissions.php') ? 'active' : ''; ?>">
            <span class="nav-icon">▤</span>
            <span class="nav-label">Submissions</span>
        </a>

        <span class="sidebar-section-label">Account</span>

        <a href="/freelancer/profile.php"
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