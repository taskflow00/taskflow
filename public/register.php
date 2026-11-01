<?php

require_once __DIR__ . '/../config/supabase.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (
        $fullName === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 6 ||
        !in_array($role, ['client', 'freelancer'], true)
    ) {
        $message = 'Please check your registration information.';
    } else {
        $data = [
            'email' => $email,
            'password' => $password,
            'data' => [
                'full_name' => $fullName,
                'role' => $role
            ]
        ];

        $curl = curl_init(SUPABASE_URL . '/auth/v1/signup');

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
                $message = 'Registration successful! Check your email if confirmation is required.';
                $messageType = 'success';
            } else {
                $message = $result['msg'] ?? $result['message'] ?? 'Registration failed. Please try again.';
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
    <title>Create Account | TaskFlow</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- TaskFlow Styles -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <script src="js/main.js" defer></script>
</head>

<body class="register-page">

    <main class="register-layout">

        <!-- Brand Section -->
        <section class="register-visual">

            <a href="index.php" class="taskflow-brand register-brand">
                <span class="brand-mark">T</span>
                <span>TaskFlow</span>
            </a>

            <div class="register-visual-content">
                <span class="register-eyebrow">ONE WORKSPACE. BETTER WORK.</span>

                <h1>Make great work<br>happen together.</h1>

                <p>
                    Whether you're managing projects or offering your skills,
                    TaskFlow gives you a place to work smarter and stay organized.
                </p>

                <div class="register-feature-list">
                    <div>
                        <span class="feature-number">01</span>
                        <span>Manage projects with clarity</span>
                    </div>

                    <div>
                        <span class="feature-number">02</span>
                        <span>Connect with the right people</span>
                    </div>

                    <div>
                        <span class="feature-number">03</span>
                        <span>Track work and measure progress</span>
                    </div>
                </div>
            </div>

            <div class="register-visual-footer">
                PLAN <span>•</span> COLLABORATE <span>•</span> DELIVER
            </div>

        </section>


        <!-- Registration Form -->
        <section class="register-form-section">

            <div class="register-form-wrapper">

                <div class="register-mobile-brand">
                    <a href="index.php" class="taskflow-brand">
                        <span class="brand-mark">T</span>
                        <span>TaskFlow</span>
                    </a>
                </div>

                <div class="register-heading">
                    <span class="register-step">GET STARTED TODAY</span>
                    <h2>Create your account</h2>
                    <p>Fill in your details to join the TaskFlow workspace.</p>
                </div>

                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?= $messageType ?>" role="alert">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php?>

                <form action="register.php" method="POST">

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input
                            type="text"
                            class="form-control taskflow-input"
                            id="full_name"
                            name="full_name"
                            placeholder="Enter your full name"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
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
                                placeholder="Create a password"
                                required>

                            <button type="button" class="password-toggle" data-target="password">
                                Show
                            </button>
                        </div>

                        <div class="form-text">Choose a password you can remember.</div>
                    </div>


                    <div class="mb-4">
                        <label for="role" class="form-label">I want to join as a</label>

                        <select
                            class="form-select taskflow-input"
                            id="role"
                            name="role"
                            required>
                            <option value="client">Client — I want to hire freelancers</option>
                            <option value="freelancer">Freelancer — I want to find projects</option>
                        </select>
                    </div>

                    <button type="submit" class="btn taskflow-button w-100">
                        Create Account <span>→</span>
                    </button>

                </form>

                <div class="register-prompt">
                    Already have an account?
                    <a href="login.php">Sign in</a>
                </div>

                <div class="register-form-footer">
                    By creating an account, you agree to use TaskFlow responsibly.
                </div>

            </div>

        </section>

    </main>

</body>

</html>