<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('freelancer');

$pageTitle = 'Dashboard';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- Welcome Section -->
<div class="mb-4">
    <h2 class="fw-bold">Welcome to TaskFlow!</h2>
    <p class="text-muted">Track your projects, tasks, and working hours.</p>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted">My Projects</h6>
            <h3>0</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted">Pending Tasks</h6>
            <h3>0</h3>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted">Hours Worked</h6>
            <h3>0 hrs</h3>
        </div>
    </div>

</div>

<!-- Recent Activity -->
<div class="card p-4">
    <h5 class="fw-bold mb-3">Recent Activity</h5>

    <p class="text-muted mb-0">
        Your recent project activities will appear here.
    </p>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>