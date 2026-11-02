<?php

require_once __DIR__ . '/../../includes/auth.php';

requireRole('client');

$pageTitle = 'Delete Project';

require_once __DIR__ . '/../../includes/supabase-api.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    $message = 'Invalid request.';
} else {

    try {

        // Verify CSRF token
        if (
            !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            throw new Exception('Invalid form submission. Please try again.');
        }

        $projectId = $_POST['project_id'] ?? '';

        // Validate project ID
        if (!preg_match('/^[0-9a-f-]{36}$/i', $projectId)) {
            throw new Exception('Invalid project ID.');
        }

        // Check project ownership and status
        $projects = supabaseRequest(
            'GET',
            'projects?select=id,status,freelancer_id&id=eq.' .
                urlencode($projectId) .
                '&client_id=eq.' .
                urlencode($_SESSION['user_id']) .
                '&limit=1'
        );

        if (empty($projects)) {
            throw new Exception('Project not found or you do not have permission to delete it.');
        }

        $project = $projects[0];

        if ($project['status'] !== 'open' || $project['freelancer_id'] !== null) {
            throw new Exception('Only open and unassigned projects can be deleted.');
        }

        // Delete project from Supabase
        $deleted = supabaseRequest(
            'DELETE',
            'projects?id=eq.' .
                urlencode($projectId) .
                '&client_id=eq.' .
                urlencode($_SESSION['user_id']) .
                '&status=eq.open&freelancer_id=is.null'
        );

        if (empty($deleted)) {
            throw new Exception('Project was not deleted. Please try again.');
        }

        header('Location: projects.php?deleted=1');
        exit();
    } catch (Exception $e) {

        $message = $e->getMessage();
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="alert alert-danger">
    <?php echo htmlspecialchars($message); ?>
</div>

<a href="projects.php" class="btn btn-secondary">
    Back to Projects
</a>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>