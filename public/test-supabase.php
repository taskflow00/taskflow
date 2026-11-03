
<?php

require_once __DIR__ . '/../config/supabase.php';

echo "<pre>";

var_dump([
    'SUPABASE_URL' => SUPABASE_URL,
    'KEY_LOADED' => !empty(SUPABASE_KEY)
]);

echo "</pre>";

echo "<h1>TaskFlow - Supabase Connection Test</h1>";

$url = SUPABASE_URL . '/auth/v1/health';

$curl = curl_init($url);

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'apikey: ' . SUPABASE_KEY
    ],
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
$errorCode = curl_errno($curl);

echo "<h3>Connection Status:</h3>";

if ($response === false) {

    echo "<p style='color:red;'>CONNECTION FAILED</p>";
    echo "<p>Error Code: " . $errorCode . "</p>";
    echo "<p>Error: " . htmlspecialchars($error) . "</p>";

} else {

    echo "<p style='color:green;'>CONNECTION SUCCESSFUL</p>";
    echo "<p>HTTP Status: " . $status . "</p>";

    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

curl_close($curl);

?>
