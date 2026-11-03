<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

$pageTitle = 'Edit Project';

require_once __DIR__ . '/../../includes/supabase-api.php';

$project = null;
$message = '';

$projectId = $_GET['id'] ?? '';

// Retrieve the project
if ($projectId === '') {

    $message = 'Invalid project ID.';
} else {

    try {

        $projects = supabaseRequest(
            'GET',
            'projects?select=id,title,description,budget,due_date,status,freelancer_id&id=eq.' .
                urlencode($projectId) .
                '&client_id=eq.' .
                urlencode($_SESSION['user_id']) .
                '&limit=1'
        );

        if (empty($projects)) {

            $message = 'Project not found or you do not have permission to access it.';
        } else {

            $project = $projects[0];

            // Only open and unassigned projects can be edited
            if ($project['status'] !== 'open' || $project['freelancer_id'] !== null) {

                $project = null;
                $message = 'This project can no longer be edited.';
            }
        }
    } catch (Exception $e) {

        $message = $e->getMessage();
    }
}

// Generate a CSRF token for this form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

// Keep the original project values as the form defaults
$formData = [
    'title' => $project['title'] ?? '',
    'description' => $project['description'] ?? '',
    'budget' => $project['budget'] ?? '',
    'due_date' => $project['due_date'] ?? ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $project !== null) {

    try {

        // Verify CSRF token
        if (
            !isset($_POST['csrf_token']) ||
            !hash_equals($csrfToken, $_POST['csrf_token'])
        ) {
            throw new Exception('Invalid form submission. Please try again.');
        }

        // Get submitted form values
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget = $_POST['budget'] ?? '';
        $dueDate = $_POST['due_date'] ?? '';

        // Keep submitted values if validation fails
        $formData = [
            'title' => $title,
            'description' => $description,
            'budget' => $budget,
            'due_date' => $dueDate
        ];

        // Validate title
        if ($title === '' || strlen($title) > 150) {
            throw new Exception('Project title is required and must not exceed 150 characters.');
        }

        // Validate description
        if ($description === '') {
            throw new Exception('Project description is required.');
        }

        // Validate budget
        if (!is_numeric($budget) || (float) $budget <= 0) {
            throw new Exception('Please enter a valid budget greater than zero.');
        }

        // Validate due date
        if ($dueDate !== '') {

            $date = DateTime::createFromFormat('!Y-m-d', $dueDate);

            if (!$date || $date->format('Y-m-d') !== $dueDate) {
                throw new Exception('Please enter a valid due date.');
            }
        } else {

            $dueDate = null;
        }

        // Update project in Supabase
        supabaseRequest(
            'PATCH',
            'projects?id=eq.' . urlencode($projectId) .
                '&client_id=eq.' . urlencode($_SESSION['user_id']) .
                '&status=eq.open&freelancer_id=is.null',
            [
                'title' => $title,
                'description' => $description,
                'budget' => (float) $budget,
                'due_date' => $dueDate
            ]
        );

        // Redirect after successful update
        header('Location: projects.php?updated=1');
        exit();
    } catch (Exception $e) {

        $message = $e->getMessage();
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="create-project-page">

    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h2 class="create-project-title">
                Edit Project
            </h2>

            <p class="create-project-description">
                Update your project information and requirements.
            </p>
        </div>

        <a href="project-details.php?id=<?php echo urlencode($projectId); ?>"
            class="create-project-back text-decoration-none">
            &larr; Back to Project
        </a>

    </div>

    <!-- Error Message -->
    <?php if ($message !== '') { ?>

        <div class="project-details-alert mb-4" role="alert">

            <strong>Unable to update project</strong>

            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>

        </div>

    <?php } ?>

    <?php if ($project !== null) { ?>

        <!-- Edit Project Card -->
        <div class="create-project-card">

            <div class="card-body p-4">

                <!-- Card Heading -->
                <div class="create-project-card-header">

                    <h5>Edit Project Information</h5>

                    <p>
                        Make changes to the fields below and save your updates.
                    </p>

                </div>

                <form method="POST">

                    <!-- CSRF Token -->
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <!-- Project Title -->
                    <div class="mb-4">

                        <label class="create-project-label" for="project-title">
                            Project Title <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="project-title"
                            name="title"
                            class="form-control taskflow-input"
                            maxlength="150"
                            placeholder="Enter your project title"
                            value="<?php echo htmlspecialchars($formData['title']); ?>"
                            required>

                    </div>

                    <!-- Project Description -->
                    <div class="mb-4">

                        <label class="create-project-label" for="project-description">
                            Project Description <span>*</span>
                        </label>

                        <textarea
                            id="project-description"
                            name="description"
                            class="form-control taskflow-input create-project-textarea"
                            placeholder="Describe your project requirements..."
                            required><?php echo htmlspecialchars($formData['description']); ?></textarea>

                        <small class="create-project-hint">
                            Provide enough information so freelancers can understand your project.
                        </small>

                    </div>

                    <!-- Budget and Due Date -->
                    <div class="row g-3">

                        <!-- Budget -->
                        <div class="col-md-6">

                            <label class="create-project-label" for="project-budget">
                                Budget (₱) <span>*</span>
                            </label>

                            <div class="input-group create-project-budget">

                                <span class="input-group-text">₱</span>

                                <input
                                    type="number"
                                    id="project-budget"
                                    name="budget"
                                    class="form-control taskflow-input"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="0.00"
                                    value="<?php echo htmlspecialchars((string) $formData['budget']); ?>"
                                    required>

                            </div>

                        </div>

                        <!-- Due Date -->
                        <div class="col-md-6">

                            <label class="create-project-label" for="project-due-date">
                                Due Date
                            </label>

                            <input
                                type="date"
                                id="project-due-date"
                                name="due_date"
                                class="form-control taskflow-input"
                                value="<?php echo htmlspecialchars($formData['due_date']); ?>">

                            <small class="create-project-hint">
                                Leave blank if your project has no deadline.
                            </small>

                        </div>

                    </div>

                    <!-- Form Actions -->
                    <div class="create-project-actions">

                        <a href="project-details.php?id=<?php echo urlencode($projectId); ?>"
                            class="taskflow-button taskflow-button-secondary text-decoration-none">
                            Cancel
                        </a>

                        <button type="submit" class="taskflow-button">
                            Save Changes
                        </button>

                    </div>

                </form>

            </div>

        </div>

    <?php } ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>