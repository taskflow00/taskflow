
<?php

// Load local .env file during development
$envFile = dirname(__DIR__) . '/.env';

if (file_exists($envFile)) {

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $_ENV[trim($name)] = trim(trim($value), "\"'");
    }
}

// Get environment variables
$supabaseUrl = getenv('SUPABASE_URL') ?: ($_ENV['SUPABASE_URL'] ?? '');

$supabaseKey = getenv('SUPABASE_PUBLISHABLE_KEY')
    ?: ($_ENV['SUPABASE_PUBLISHABLE_KEY'] ?? '');

// Supabase connection details
define('SUPABASE_URL', $supabaseUrl);
define('SUPABASE_KEY', $supabaseKey);
