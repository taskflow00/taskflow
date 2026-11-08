<?php

require_once __DIR__ . '/../config/supabase.php';

require_once __DIR__ . '/../includes/session.php';
tf_session_start();

// Redirect already logged-in users
if (
    !empty($_SESSION['user_id']) &&
    !empty($_SESSION['access_token']) &&
    !empty($_SESSION['role'])
) {
    header('Location: index.php');
    exit;
}

$message = '';

if (isset($_POST['login'])) {

    // Get form values
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {

        $message = 'Please enter a valid email and password.';
    } else {

        // Prepare login data
        $data = [
            'email' => $email,
            'password' => $password
        ];

        // Send login request to Supabase Auth
        $curl = curl_init(SUPABASE_URL . '/auth/v1/token?grant_type=password');

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . SUPABASE_KEY
        ]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Check connection
        if ($response === false) {

            $message = 'Connection error: ' . curl_error($curl);
        } else {

            $result = json_decode($response, true);

            // Check successful authentication
            if (
                $status >= 200 &&
                $status < 300 &&
                !empty($result['access_token']) &&
                !empty($result['user']['id'])
            ) {

                // Save user information
                $_SESSION['access_token'] = $result['access_token'];
                $_SESSION['refresh_token'] = $result['refresh_token'] ?? '';
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['email'] = $result['user']['email'] ?? $email;

                // Get user profile from Supabase
                $profileCurl = curl_init(
                    SUPABASE_URL . '/rest/v1/profiles?id=eq.' .
                        urlencode($_SESSION['user_id']) .
                        '&select=full_name,role'
                );

                curl_setopt($profileCurl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($profileCurl, CURLOPT_HTTPHEADER, [
                    'apikey: ' . SUPABASE_KEY,
                    'Authorization: Bearer ' . $_SESSION['access_token']
                ]);

                $profileResponse = curl_exec($profileCurl);

                if ($profileResponse === false) {

                    $message = 'Unable to connect to your account profile.';
                } else {

                    $profile = json_decode($profileResponse, true);

                    if (empty($profile) || !isset($profile[0]['role'])) {

                        $message = 'Unable to retrieve your account profile.';
                    } else {

                        $profile = $profile[0];

                        $_SESSION['full_name'] = $profile['full_name'] ?? '';
                        $_SESSION['role'] = $profile['role'];

                        // Login successful
                        // Redirect to the main entry point for role-based routing.
                        header('Location: index.php');
                        exit;
                    }
                }
            } else {

                // Display Supabase authentication error
                $message = $result['msg']
                    ?? $result['message']
                    ?? $result['error_description']
                    ?? 'Invalid email or password.';
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | TaskFlow</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- TaskFlow Styles -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <script src="js/main.js" defer></script>
</head>

<body class="login-page">

    <div class="container-fluid min-vh-100">
        <div class="row min-vh-100">

            <!-- Login Section -->
            <div class="col-lg-6 login-left">
                <div class="login-wrapper">

                    <a href="index.php" class="taskflow-brand">
                        <span class="brand-mark">T</span>
                        <span>TaskFlow</span>
                    </a>

                    <div class="login-content">
                        <div class="welcome-chip">
                            <span></span> YOUR WORKSPACE AWAITS
                        </div>

                        <h1>Welcome back.</h1>
                        <p class="login-description">
                            Sign in to continue managing your projects,
                            collaborating with your team, and getting work done.
                        </p>

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">

                            <div class="mb-4">
                                <label for="email" class="form-label">Email address</label>
                                <input
                                    type="email"
                                    class="form-control taskflow-input"
                                    id="email"
                                    name="email"
                                    placeholder="name@example.com"
                                    required>
                            </div>


                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>

                                <div class="password-wrapper">
                                    <input
                                        type="password"
                                        class="form-control taskflow-input"
                                        id="password"
                                        name="password"
                                        placeholder="Enter your password"
                                        required>

                                    <button type="button" class="password-toggle" data-target="password">
                                        Show
                                    </button>
                                </div>
                            </div>


                            <button type="submit" name="login" class="btn taskflow-button w-100">
                                Sign In <span>→</span>
                            </button>

                        </form>

                        <div class="register-prompt">
                            Don't have an account?
                            <a href="register.php">Create an account</a>
                        </div>
                    </div>

                    <div class="login-bottom">
                        © 2026 TaskFlow. All rights reserved.
                    </div>

                </div>
            </div>

            <!-- Visual Section -->
            <div class="col-lg-6 login-right">
                <div class="visual-orb orb-one"></div>
                <div class="visual-orb orb-two"></div>

                <div class="right-content">

                    <div class="visual-label">
                        <span class="live-dot"></span>
                        BUILT FOR BETTER COLLABORATION
                    </div>

                    <h2>Good work starts<br>with a clear plan.</h2>

                    <p class="right-description">
                        Bring clients, freelancers, projects, and progress
                        together in one organized workspace.
                    </p>

                    <div class="workspace-preview">

                        <div class="preview-top">
                            <div>
                                <small>YOUR WORKSPACE</small>
                                <h5>Project overview</h5>
                            </div>
                            <span class="preview-menu">•••</span>
                        </div>

                        <div class="preview-project">
                            <div class="preview-icon">TF</div>
                            <div class="flex-grow-1">
                                <strong>Website Development</strong>
                                <small>Project in progress</small>
                            </div>
                            <span class="preview-percent">75%</span>
                        </div>

                        <div class="preview-progress">
                            <div></div>
                        </div>

                        <div class="preview-divider"></div>

                        <div class="preview-bottom">
                            <div>
                                <strong>12</strong>
                                <small>Completed tasks</small>
                            </div>
                            <div>
                                <strong>04</strong>
                                <small>In progress</small>
                            </div>
                            <div>
                                <strong>02</strong>
                                <small>Collaborators</small>
                            </div>
                        </div>

                    </div>

                    <div class="right-caption">
                        <span>01</span>
                        PLAN &nbsp; / &nbsp; COLLABORATE &nbsp; / &nbsp; DELIVER
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>

</html>
