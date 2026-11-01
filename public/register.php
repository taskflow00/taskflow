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
    <title>Register | TaskFlow</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="register-container">
        <h1>TaskFlow</h1>
        <p>Create your account</p>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">

            <label>Full Name</label>
            <input type="text" name="full_name" placeholder="Enter your full name" required>

            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Create a password" required>

            <label>Register as</label>
            <select name="role" required>
                <option value="client">Client</option>
                <option value="freelancer">Freelancer</option>
            </select>

            <button type="submit">Create Account</button>

        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>

</body>

</html>