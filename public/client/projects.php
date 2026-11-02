<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

$pageTitle = 'My Projects';

require_once __DIR__ . '/../../includes/header.php';

?>

<!-- Page Heading -->
<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="fw-bold">My Projects</h2>
        <p class="text-muted mb-0">
            Manage and monitor all your projects.
        </p>
    </div>

    <a href="create-project.php" class="btn taskflow-button">
        + Create Project
    </a>

</div>

<!-- Project Filters -->
<div class="card p-3 mb-4">

    <div class="d-flex gap-2 flex-wrap">

        <button class="btn btn-sm btn-outline-secondary active">
            All Projects
        </button>

        <button class="btn btn-sm btn-outline-secondary">
            Open
        </button>

        <button class="btn btn-sm btn-outline-secondary">
            In Progress
        </button>

        <button class="btn btn-sm btn-outline-secondary">
            Completed
        </button>

    </div>

</div>

<!-- Projects Table -->
<div class="card p-4">

    <div class="mb-3">
        <h5 class="fw-bold mb-1">All Projects</h5>
        <p class="text-muted mb-0">
            View and manage your posted projects.
        </p>
    </div>

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <td colspan="5" class="text-center py-5">

                        <h6 class="fw-bold">No projects yet</h6>

                        <p class="text-muted mb-3">
                            You haven't created any projects.
                        </p>

                        <a href="create-project.php" class="btn taskflow-button btn-sm">
                            Create Your First Project
                        </a>

                    </td>
                </tr>

            </tbody>

        </table>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>