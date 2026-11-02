
<?php

require_once __DIR__ . '/../config/supabase.php';

function supabaseRequest($method, $table, $data = null)
{
    $accessToken = $_SESSION['access_token'] ?? '';

    if (empty($accessToken)) {
        throw new Exception('Session expired. Please log in again.');
    }

    if (empty(SUPABASE_URL) || empty(SUPABASE_KEY)) {
        throw new Exception('Supabase configuration is missing.');
    }

    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . $table;

    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    $curl = curl_init($url);

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers
    ]);

    if ($data !== null) {
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            json_encode($data)
        );
    }

    $response = curl_exec($curl);

    if ($response === false) {
        $error = curl_error($curl);


        throw new Exception('Connection error: ' . $error);
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $result = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        $message = $result['message'] ?? 'Supabase request failed.';
        throw new Exception($message);
    }

    return $result;
}
