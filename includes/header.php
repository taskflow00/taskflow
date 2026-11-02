<?php

// Page title
$pageTitle = $pageTitle ?? 'Dashboard';

// Logged-in user information
$userName = $_SESSION['full_name'] ?? 'User';
$userRole = $_SESSION['role'] ?? '';

// Select sidebar based on user role
$sidebars = [
    'client' => 'client-sidebar.php',
    'freelancer' => 'freelancer-sidebar.php',
    'admin' => 'admin-sidebar.php'
];

if (!isset($sidebars[$userRole])) {
    http_response_code(403);
    exit('Invalid user role.');
}

// User initials
// User initials
$nameParts = explode(' ', trim($userName));

$initials = strtoupper(substr($nameParts[0], 0, 1));

if (count($nameParts) > 1) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
}

// Profile page URL
$profileUrl = '/' . $userRole . '/profile.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle) ?> | TaskFlow</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- TaskFlow CSS -->
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/dashboard.css">

</head>

<body>

    <div class="dashboard-layout">

        <!-- Load sidebar based on user role -->
        <?php require_once __DIR__ . '/' . $sidebars[$userRole]; ?>

        <!-- Main dashboard -->
        <div class="dashboard-main">

            <!-- Topbar -->
            <header class="dashboard-topbar">

                <div class="topbar-left">


                    <button
                        type="button"
                        class="sidebar-toggle"
                        id="sidebarToggle"
                        aria-label="Toggle sidebar"
                        title="Toggle sidebar">
                        <span></span>
                    </button>


                    <h5 class="mb-0 fw-bold">
                        <?= htmlspecialchars($pageTitle) ?>
                    </h5>

                </div>


                <div class="topbar-right">

                    <a href="<?php echo $profileUrl; ?>" class="topbar-user text-decoration-none">

                        <div class="topbar-avatar">
                            <?php echo htmlspecialchars($initials); ?>
                        </div>

                        <div class="topbar-user-info">
                            <strong>
                                <?php echo htmlspecialchars($userName); ?>
                            </strong>

                            <span class="topbar-role">
                                <?php echo htmlspecialchars(ucfirst($userRole)); ?>
                            </span>
                        </div>

                        <span class="topbar-profile-icon">⌄</span>

                    </a>

                </div>


            </header>

            <!-- Page content starts here -->
            <main class="dashboard-content">