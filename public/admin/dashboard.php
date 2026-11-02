<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('admin');

$pageTitle = 'Admin Dashboard';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- Welcome Section -->
<div class="mb-4">
    <h2 class="fw-bold">Welcome to TaskFlow Admin!</h2>
    <p class="text-muted">Monitor users, projects, and platform activity.</p>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted">Total Users</h6>
            <h3>0</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted">Total Projects</h6>
            <h3>0</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted">Active Projects</h6>
            <h3>0</h3>
        </div>
    </div>

</div>

<!-- Recent Activity -->
<div class="card p-4">
    <h5 class="fw-bold mb-3">Recent Activity</h5>

    <p class="text-muted mb-0">
        Platform activities will appear here.
    </p>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>