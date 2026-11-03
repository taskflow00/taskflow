<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

$pageTitle = 'My Projects';

require_once __DIR__ . '/../../includes/supabase-api.php';

$projects = [];
$message = '';

$filteredProjects = [];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $projects = supabaseRequest(
        'GET',
        'projects?select=id,title,budget,due_date,status,created_at,freelancer_id&client_id=eq.' .
            urlencode($_SESSION['user_id']) .
            '&order=created_at.desc'
    );
} catch (Exception $e) {
    $message = $e->getMessage();
}

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

<!-- Success Messages -->
<?php if (isset($_GET['updated']) && $_GET['updated'] === '1') { ?>

    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Project successfully updated!

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

<?php } elseif (isset($_GET['deleted']) && $_GET['deleted'] === '1') { ?>

    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Project successfully deleted!

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

<?php } ?>


<?php

$selectedStatus = $_GET['status'] ?? 'all';

$allowedStatuses = ['all', 'open', 'in_progress', 'completed'];

if (!in_array($selectedStatus, $allowedStatuses, true)) {
    $selectedStatus = 'all';
}

// Filter projects based on selected status
if ($selectedStatus === 'all') {
    $filteredProjects = $projects;
} else {
    $filteredProjects = [];

    foreach ($projects as $project) {
        if ($project['status'] === $selectedStatus) {
            $filteredProjects[] = $project;
        }
    }
}

?>

<!-- Project Filters -->
<div class="card p-3 mb-4">

    <div class="d-flex gap-2 flex-wrap">

        <a href="projects.php?status=all"
            class="btn btn-sm btn-outline-secondary <?php echo $selectedStatus === 'all' ? 'active' : ''; ?>">
            All Projects
        </a>

        <a href="projects.php?status=open"
            class="btn btn-sm btn-outline-secondary <?php echo $selectedStatus === 'open' ? 'active' : ''; ?>">
            Open
        </a>

        <a href="projects.php?status=in_progress"
            class="btn btn-sm btn-outline-secondary <?php echo $selectedStatus === 'in_progress' ? 'active' : ''; ?>">
            In Progress
        </a>

        <a href="projects.php?status=completed"
            class="btn btn-sm btn-outline-secondary <?php echo $selectedStatus === 'completed' ? 'active' : ''; ?>">
            Completed
        </a>

    </div>

</div>


<!-- Projects Table -->
<div class="card p-4">

    <div class="mb-3">
        <h5 class="fw-bold mb-1">
            <?php
            if ($selectedStatus === 'all') {
                echo 'All Projects';
            } elseif ($selectedStatus === 'open') {
                echo 'Open Projects';
            } elseif ($selectedStatus === 'in_progress') {
                echo 'In Progress Projects';
            } elseif ($selectedStatus === 'completed') {
                echo 'Completed Projects';
            }
            ?>
        </h5>
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

                <?php if ($message !== '') { ?>

                    <tr>
                        <td colspan="5" class="text-center py-5 text-danger">
                            <?php echo htmlspecialchars($message); ?>
                        </td>
                    </tr>

                <?php } elseif (empty($projects)) { ?>

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

                <?php } elseif (empty($filteredProjects)) { ?>

                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <h6 class="fw-bold">No matching projects</h6>
                            <p class="text-muted mb-0">
                                There are no projects under this status.
                            </p>
                        </td>
                    </tr>

                <?php } else { ?>

                    <?php foreach ($filteredProjects as $project) { ?>

                        <tr>
                            <td>
                                <strong>
                                    <?php echo htmlspecialchars($project['title']); ?>
                                </strong>
                            </td>

                            <td>
                                ₱<?php echo number_format((float)$project['budget'], 2); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $project['status']))); ?>
                            </td>

                            <td>
                                <?php echo date('M d, Y', strtotime($project['created_at'])); ?>
                            </td>

                            <td>
                                <a href="project-details.php?id=<?php echo urlencode($project['id']); ?>"
                                    class="btn btn-sm btn-outline-secondary">
                                    View
                                </a>

                                <?php if ($project['status'] === 'open' && $project['freelancer_id'] === null) { ?>

                                    <a href="edit-project.php?id=<?php echo urlencode($project['id']); ?>"
                                        class="btn btn-sm btn-success">
                                        Edit
                                    </a>

                                <?php } ?>

                                <form method="POST" action="delete-project.php"
                                    class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this project? This action cannot be undone.');">

                                    <input
                                        type="hidden"
                                        name="project_id"
                                        value="<?php echo htmlspecialchars($project['id']); ?>">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>


                                </form>
                            </td>
                        </tr>

                    <?php } ?>

                <?php } ?>

            </tbody>

        </table>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>