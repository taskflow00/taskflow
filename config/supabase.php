<?php

// Load environment variables from the .env file
$envFile = dirname(__DIR__) . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $_ENV[trim($name)] = trim(trim($value), "\"'");
    }
}

// Supabase connection details
define('SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? '');
define('SUPABASE_KEY', $_ENV['SUPABASE_PUBLISHABLE_KEY'] ?? '');