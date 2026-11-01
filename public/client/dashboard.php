<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

$pageTitle = 'Dashboard';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- Dashboard Welcome -->
<div class="mb-4">
    <h2 class="fw-bold">Welcome to TaskFlow!</h2>
    <p class="text-muted">Manage your projects and work with freelancers.</p>
</div>

<!-- Dashboard Content -->
<div class="row g-3">

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

    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="text-muted">Pending Submissions</h6>
            <h3>0</h3>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>