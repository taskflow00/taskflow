
<?php

require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$role = $_SESSION['role'] ?? '';

if ($role === 'client') {

    header('Location: /client/dashboard.php');
    exit;
} elseif ($role === 'freelancer') {

    header('Location: /freelancer/dashboard.php');
    exit;
} elseif ($role === 'admin') {

    header('Location: /admin/dashboard.php');
    exit;
} else {

    http_response_code(403);
    exit('Invalid user role.');
}
