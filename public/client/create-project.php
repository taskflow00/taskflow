<?php

require_once __DIR__ . '/../../includes/auth.php';
requireRole('client');

require_once __DIR__ . '/../../includes/supabase-api.php';

$pageTitle = 'Create Project';

$message = '';
$success = '';

$title = '';
$description = '';
$budget = '';
$due_date = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form values
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $due_date = trim($_POST['due_date'] ?? '');

    // Validate form data
    if ($title === '' || strlen($title) > 150) {

        $message = 'Please enter a project title (maximum 150 characters).';
    } elseif ($description === '') {

        $message = 'Please enter a project description.';
    } elseif (!is_numeric($budget) || (float)$budget <= 0) {

        $message = 'Please enter a valid project budget.';
    } else {

        try {

            // Prepare project data
            $projectData = [
                'client_id' => $_SESSION['user_id'],
                'title' => $title,
                'description' => $description,
                'budget' => (float)$budget,
                'due_date' => $due_date !== '' ? $due_date : null
            ];

            // Insert project into Supabase
            $result = supabaseRequest(
                'POST',
                'projects',
                $projectData
            );

            $success = 'Your project has been created successfully!';

            // Clear form values
            $title = '';
            $description = '';
            $budget = '';
        } catch (Exception $e) {

            if (str_contains($e->getMessage(), 'projects_client_normalized_title_uidx')) {
                $message = 'You already have a project with this title. Please choose a different title.';
            } else {
                $message = $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>


<div class="create-project-page">

    <!-- Page heading -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h2 class="create-project-title">Create a New Project</h2>
            <p class="create-project-description">
                Describe your project and let freelancers know what you need.
            </p>
        </div>

        <a href="projects.php" class="create-project-back taskflow-button taskflow-button-secondary">
            <span>&larr;</span> Back to Projects
        </a>

    </div>

    <!-- Project form -->
    <div class="card create-project-card">

        <div class="card-body p-4 p-md-5">

            <div class="create-project-card-header">
                <h5>Project Information</h5>
                <p>Fill out the details below to get your project started.</p>
            </div>


            <?php if (!empty($success)) { ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success) ?>
                </div>
            <?php } ?>

            <?php if (!empty($message)) { ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($message) ?>
                </div>
            <?php } ?>


            <form action="" method="POST">

                <!-- Title -->
                <div class="mb-4">

                    <label for="title" class="form-label create-project-label">
                        Project Title <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?php echo htmlspecialchars($title) ?>"
                        class="form-control taskflow-input"
                        placeholder="e.g. Build a responsive business website"
                        maxlength="150"
                        required>

                    <small class="create-project-hint">
                        Give your project a clear and descriptive title.
                    </small>

                </div>

                <!-- Description -->
                <div class="mb-4">

                    <label for="description" class="form-label create-project-label">
                        Project Description <span>*</span>
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        class="form-control taskflow-input create-project-textarea"
                        placeholder="Describe your project requirements, goals, and expected output..."
                        required></textarea>

                    <small class="create-project-hint">
                        Explain what needs to be done and what you expect to receive.
                    </small>

                </div>

                <!-- Budget -->
                <div class="mb-4">

                    <label for="budget" class="form-label create-project-label">
                        Project Budget <span>*</span>
                    </label>

                    <div class="input-group create-project-budget">

                        <span class="input-group-text">₱</span>

                        <input
                            type="number"
                            id="budget"
                            name="budget"
                            value="<?php echo htmlspecialchars($budget) ?>"
                            class="form-control taskflow-input"
                            placeholder="5000"
                            min="1"
                            step="0.01"
                            required>

                    </div>

                    <small class="create-project-hint">
                        Enter the amount you're willing to spend on this project.
                    </small>

                </div>


                <!-- Due Date -->
                <div class="mb-4">

                    <label for="due_date" class="form-label create-project-label">
                        Project Deadline
                    </label>

                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        required
                        class="form-control taskflow-input">

                    <small class="create-project-hint">
                        Select the expected completion date for your project.
                    </small>

                </div>


                <!-- Actions -->
                <div class="create-project-actions">

                    <a href="projects.php"
                        class="taskflow-button taskflow-button-sm taskflow-button-secondary">
                        Cancel
                    </a>

                    <button type="submit" class="taskflow-button">
                        Create Project <span>&rarr;</span>
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>