<?php

require_once __DIR__ . '/../config/supabase.php';

if (empty(SUPABASE_URL) || empty(SUPABASE_KEY)) {
    die("Supabase configuration is missing.");
}

$curl = curl_init(SUPABASE_URL . '/rest/v1/profiles?select=id&limit=1');

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY
    ],
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($curl);

// Get connection information before ending the request
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);

if ($response === false) {
    die("Connection error: " . htmlspecialchars($error));
}

if ($status >= 200 && $status < 300) {
    echo "TaskFlow connected to Supabase!";
} else {
    echo "Supabase responded with HTTP Status: " . $status;
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}
