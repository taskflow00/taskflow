
<?php

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
function requireLogin()
{

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['access_token'])) {
        header("Location: /login.php");
        exit();
    }
}

// Check user's role
function requireRole($role)
{

    requireLogin();

    if (!isset($_SESSION['role']) || $_SESSION['role'] != $role) {
        echo "Access denied!";
        exit();
    }
}

?>
