<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

$pageTitle = 'Project Details';

require_once __DIR__ . '/../../includes/supabase-api.php';

// Create a CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get the project ID from the URL
$projectId = $_GET['id'] ?? '';

$project = null;
$freelancer = null;
$message = '';

try {

    if ($projectId === '') {
        throw new Exception('Project ID is missing.');
    }

    // Retrieve the selected project belonging to this client
    $projectResult = supabaseRequest(
        'GET',
        'projects?select=id,title,description,budget,due_date,status,created_at,freelancer_id&id=eq.' .
            urlencode($projectId) .
            '&client_id=eq.' .
            urlencode($_SESSION['user_id']) .
            '&limit=1'
    );

    if (empty($projectResult)) {
        throw new Exception('Project not found.');
    }

    $project = $projectResult[0];

    // Retrieve the assigned freelancer's profile
    if (!empty($project['freelancer_id'])) {

        try {

            $freelancerResult = supabaseRequest(
                'GET',
                'profiles?select=id,full_name&id=eq.' .
                    urlencode($project['freelancer_id']) .
                    '&limit=1'
            );

            if (!empty($freelancerResult)) {
                $freelancer = $freelancerResult[0];
            }
        } catch (Exception $e) {

            // Keep displaying the project even if the profile lookup fails
            $freelancer = null;
        }
    }
} catch (Exception $e) {

    $message = $e->getMessage();
}

// Convert the project status into a readable label and CSS class
$statusLabels = [
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

$statusClasses = [
    'open' => 'status-open',
    'in_progress' => 'status-in-progress',
    'completed' => 'status-completed',
    'cancelled' => 'status-cancelled'
];

$status = $project['status'] ?? 'open';

$statusLabel = $statusLabels[$status] ?? 'Unknown';
$statusClass = $statusClasses[$status] ?? 'status-unknown';

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="project-details-page">

    <!-- Page Heading -->
    <div class="project-details-heading">

        <div>
            <span class="project-details-eyebrow">PROJECT OVERVIEW</span>

            <h2 class="project-details-title">
                Project Details
            </h2>

            <p class="project-details-description">
                Review your project's information, budget, and assignment.
            </p>
        </div>

        <a href="projects.php"
            class="taskflow-button taskflow-button-secondary">
            <span aria-hidden="true">&larr;</span>
            Back to Projects
        </a>

    </div>

    <?php if ($message !== '') { ?>

        <!-- Error Message -->
        <div class="project-details-alert" role="alert">
            <strong>Unable to load project</strong>
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>

    <?php } elseif ($project !== null) { ?>

        <!-- Main Project Card -->
        <section class="project-details-card">

            <!-- Project Header -->
            <div class="project-details-card-header">

                <div class="project-details-project-heading">

                    <span class="project-details-eyebrow">
                        PROJECT
                    </span>

                    <h3 class="project-details-project-title">
                        <?php echo htmlspecialchars($project['title']); ?>
                    </h3>

                </div>

                <span class="project-details-status <?php echo htmlspecialchars($statusClass); ?>">
                    <span class="project-details-status-dot"></span>
                    <?php echo htmlspecialchars($statusLabel); ?>
                </span>

            </div>

            <!-- Project Description -->
            <div class="project-details-section">

                <h4 class="project-details-section-title">
                    About this project
                </h4>

                <p class="project-details-project-description">
                    <?php
                    echo !empty($project['description'])
                        ? nl2br(htmlspecialchars($project['description']))
                        : 'No description has been provided for this project.';
                    ?>
                </p>

            </div>

            <!-- Project Information -->
            <div class="project-details-section">

                <h4 class="project-details-section-title">
                    Project information
                </h4>

                <div class="row g-3">

                    <!-- Budget -->
                    <div class="col-md-6 col-xl-3">
                        <div class="project-details-info">

                            <span class="project-details-info-label">
                                Budget
                            </span>

                            <strong class="project-details-info-value">
                                <?php
                                echo $project['budget'] !== null
                                    ? '₱' . number_format((float) $project['budget'], 2)
                                    : 'Not set';
                                ?>
                            </strong>

                        </div>
                    </div>

                    <!-- Due Date -->
                    <div class="col-md-6 col-xl-3">
                        <div class="project-details-info">

                            <span class="project-details-info-label">
                                Due date
                            </span>

                            <strong class="project-details-info-value">
                                <?php
                                echo !empty($project['due_date'])
                                    ? date('M d, Y', strtotime($project['due_date']))
                                    : 'No deadline';
                                ?>
                            </strong>

                        </div>
                    </div>

                    <!-- Date Created -->
                    <div class="col-md-6 col-xl-3">
                        <div class="project-details-info">

                            <span class="project-details-info-label">
                                Date created
                            </span>

                            <strong class="project-details-info-value">
                                <?php
                                echo !empty($project['created_at'])
                                    ? date('M d, Y', strtotime($project['created_at']))
                                    : 'Not available';
                                ?>
                            </strong>

                        </div>
                    </div>

                    <!-- Assigned Freelancer -->
                    <div class="col-md-6 col-xl-3">
                        <div class="project-details-info">

                            <span class="project-details-info-label">
                                Assigned freelancer
                            </span>

                            <strong class="project-details-info-value">
                                <?php
                                echo htmlspecialchars(
                                    $freelancer['full_name'] ?? 'Not assigned'
                                );
                                ?>
                            </strong>

                        </div>
                    </div>

                </div>

            </div>

            <!-- Project Actions -->
            <?php if ($project['status'] === 'open' && $project['freelancer_id'] === null) { ?>

                <div class="project-details-actions">

                    <div class="project-details-actions-text">
                        <strong>Manage this project</strong>
                        <span>You can edit or delete this project while it is still open and unassigned.</span>
                    </div>

                    <div class="project-details-actions-buttons">

                        <a href="edit-project.php?id=<?php echo urlencode($project['id']); ?>"
                            class="taskflow-button">
                            Edit Project
                        </a>

                        <form method="POST"
                            action="delete-project.php"
                            onsubmit="return confirm('Are you sure you want to delete this project? This action cannot be undone.');">

                            <input
                                type="hidden"
                                name="project_id"
                                value="<?php echo htmlspecialchars($project['id']); ?>">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <button type="submit" class="taskflow-button taskflow-button-danger">
                                Delete Project
                            </button>

                        </form>

                    </div>

                </div>

            <?php } ?>

        </section>

    <?php } ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>