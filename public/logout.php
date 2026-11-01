
<?php

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Clear session data
session_unset();

// Destroy session
session_destroy();

// Return to login page
header("Location: /login.php");
exit();

?>
