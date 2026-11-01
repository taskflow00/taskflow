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
</head>

<body>

    <div class="login-container">
        <h1>TaskFlow</h1>
        <p>Welcome back! Please login.</p>

        <form method="POST">

            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <button type="submit" name="login">Login</button>

        </form>

        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>

</body>

</html>