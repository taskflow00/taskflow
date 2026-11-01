<?php

require_once __DIR__ . '/../config/supabase.php';

session_start();

$message = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $message = 'Please enter a valid email and password.';
    } else {
        $data = [
            'email' => $email,
            'password' => $password
        ];

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

        if ($response === false) {
            $message = 'Connection error: ' . curl_error($curl);
        } else {
            $result = json_decode($response, true);
            if ($status >= 200 && $status < 300) {
                session_regenerate_id(true);

                $_SESSION['access_token'] = $result['access_token'];
                $_SESSION['refresh_token'] = $result['refresh_token'];
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['email'] = $result['user']['email'];
            } else {
                $message = $result['msg'] ?? $result['message'] ?? 'Invalid email or password.';
            }

            $profileCurl = curl_init(
                SUPABASE_URL . '/rest/v1/profiles?id=eq=' . urlencode($result['user']['id']) . '&select=full_name,role'
            );

            curl_setopt($profileCurl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($profileCurl, CURLOPT_HTTPHEADER, [
                'apikey: ' . SUPABASE_KEY,
                'Authorization: Bearer ' . $result['access_token']
            ]);

            $profileResponse = curl_exec($profileCurl);

            $profile = json_decode($profileResponse, true);

            if ($profileResponse === false || empty($profile)) {
                $message = 'Unable to retrieve your account profile.';
            } else {
                $profile = $profile[0];

                $_SESSION['full_name'] = $profile['full_name'];
                $_SESSION['role'] = $profile['role'];
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
    <title>Login | TaskFlow</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="auth-page">

    <main class="auth-layout">

        <section class="auth-form-section">

            <div class="auth-brand">
                <div class="brand-icon">T</div>
                <span>TaskFlow</span>
            </div>

            <div class="auth-form-wrapper">

                <div class="auth-heading">
                    <span class="eyebrow">WELCOME BACK</span>
                    <h1>Sign in to your account</h1>
                    <p>Manage your projects and get things done.</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="auth-message">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form">

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                            required>
                    </div>

                    <div class="form-group">
                        <div class="password-label">
                            <label for="password">Password</label>
                            <a href="#" class="forgot-link">Forgot password?</a>
                        </div>

                        <div class="password-input">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required>
                            <button type="button" id="togglePassword" class="toggle-password">
                                Show
                            </button>
                        </div>
                    </div>

                    <button type="submit" name="login" class="auth-submit">
                        Sign in
                        <span>→</span>
                    </button>

                </form>

                <p class="auth-switch">
                    Don't have an account?
                    <a href="register.php">Create an account</a>
                </p>

            </div>

            <footer class="auth-footer">
                © 2026 TaskFlow. All rights reserved.
            </footer>

        </section>

        <section class="auth-visual-section">

            <div class="visual-content">
                <div class="visual-badge">
                    <span class="status-dot"></span>
                    PROJECT MANAGEMENT MADE SIMPLE
                </div>

                <h2>Work smarter.<br>Achieve more.</h2>

                <p>
                    Bring your projects, tasks, and collaboration
                    together in one organized workspace.
                </p>

                <div class="visual-card">
                    <div class="visual-card-header">
                        <span>Project Overview</span>
                        <span class="visual-live">● Live</span>
                    </div>

                    <div class="visual-project">
                        <div class="project-icon">W</div>
                        <div>
                            <strong>Website Redesign</strong>
                            <small>Project progress</small>
                        </div>
                        <span class="project-percent">75%</span>
                    </div>

                    <div class="progress-track">
                        <div class="progress-fill"></div>
                    </div>

                    <div class="visual-stats">
                        <div>
                            <strong>12</strong>
                            <small>Tasks completed</small>
                        </div>
                        <div>
                            <strong>4</strong>
                            <small>In progress</small>
                        </div>
                        <div>
                            <strong>2</strong>
                            <small>Team members</small>
                        </div>
                    </div>
                </div>

                <div class="visual-caption">
                    One workspace. Every project. Better results.
                </div>
            </div>

        </section>

    </main>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            this.textContent = isPassword ? 'Hide' : 'Show';
        });
    </script>

</body>

</html>