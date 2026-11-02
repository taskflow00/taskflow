<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

$pageTitle = 'Edit Project';

require_once __DIR__ . '/../../includes/supabase-api.php';

$project = null;
$message = '';

$projectId = $_GET['id'] ?? '';

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

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $budget = $_POST['budget'] ?? '';
        $dueDate = $_POST['due_date'] ?? '';

        // Validate title
        if ($title === '' || strlen($title) > 150) {
            throw new Exception('Project title is required and must not exceed 150 characters.');
        }

        // Validate description
        if ($description === '') {
            throw new Exception('Project description is required.');
        }

        // Validate budget
        if (!is_numeric($budget) || (float)$budget <= 0) {
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
                'budget' => (float)$budget,
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

<h2 class="fw-bold">Edit Project</h2>
<p class="text-muted">Update your project details.</p>

<?php if ($message !== '') { ?>

    <div class="alert alert-danger">
        <?php echo htmlspecialchars($message); ?>
    </div>

<?php } elseif ($project !== null) { ?>

    <div class="card p-4">

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($csrfToken); ?>">

            <!-- Project Title -->
            <div class="mb-3">

                <label class="form-label fw-semibold">Project Title</label>

                <input
                    type="text"
                    name="title"
                    class="form-control"
                    maxlength="150"
                    value="<?php echo htmlspecialchars($project['title']); ?>"
                    required>

            </div>

            <!-- Description -->
            <div class="mb-3">

                <label class="form-label fw-semibold">Description</label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="5"
                    required><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>

            </div>

            <!-- Budget -->
            <div class="mb-3">

                <label class="form-label fw-semibold">Budget (₱)</label>

                <input
                    type="number"
                    name="budget"
                    class="form-control"
                    min="0.01"
                    step="0.01"
                    value="<?php echo htmlspecialchars($project['budget'] ?? ''); ?>"
                    required>

            </div>

            <!-- Due Date -->
            <div class="mb-4">

                <label class="form-label fw-semibold">Due Date</label>

                <input
                    type="date"
                    name="due_date"
                    class="form-control"
                    value="<?php echo htmlspecialchars($project['due_date'] ?? ''); ?>">

            </div>

            <!-- Buttons -->
            <div class="d-flex gap-2">

                <button type="submit" class="btn btn-success">
                    Save Changes
                </button>

                <a href="projects.php" class="btn btn-outline-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </div>

<?php } ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>