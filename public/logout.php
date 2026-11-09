
<?php

require_once __DIR__ . '/../includes/session.php';
tf_session_start();

// Clear session data (cookie is removed on shutdown)
tf_session_destroy();

// Return to login page
header("Location: /login.php");
exit();

?>
