<?php

/**
 * Stateless cookie-based session (works on Vercel, where requests may hit
 * different container instances and file-based sessions get lost).
 *
 * Session data is encrypted (AES-256-GCM) and stored in the browser cookie.
 * Requires the SESSION_SECRET environment variable (long random string).
 *
 * Exposes: $_SESSION, tf_session_start(), tf_session_destroy()
 */

const TF_COOKIE = 'tf_session';
const TF_LIFETIME = 60 * 60 * 24 * 7; // 7 days

function tf_secret_key(): string
{
    $secret = getenv('SESSION_SECRET') ?: ($_ENV['SESSION_SECRET'] ?? '');

    if ($secret === '') {
        http_response_code(500);
        exit('SESSION_SECRET is not configured.');
    }

    return hash('sha256', $secret, true);
}

function tf_encrypt(array $data): string
{
    $iv = random_bytes(12);
    $plain = json_encode($data);
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', tf_secret_key(), OPENSSL_RAW_DATA, $iv, $tag);

    return rtrim(strtr(base64_encode($iv . $tag . $cipher), '+/', '-_'), '=');
}

function tf_decrypt(string $token): ?array
{
    $raw = base64_decode(strtr($token, '-_', '+/'), true);

    if ($raw === false || strlen($raw) < 29) {
        return null;
    }

    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $cipher = substr($raw, 28);

    $plain = openssl_decrypt($cipher, 'aes-256-gcm', tf_secret_key(), OPENSSL_RAW_DATA, $iv, $tag);

    if ($plain === false) {
        return null;
    }

    $data = json_decode($plain, true);

    if (!is_array($data) || ($data['_exp'] ?? 0) < time()) {
        return null;
    }

    return $data;
}

function tf_cookie_options(int $expires): array
{
    return [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => true,   // Vercel is always HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function tf_session_start(): void
{
    static $started = false;

    if ($started) {
        return;
    }

    $started = true;
    $_SESSION = [];

    if (!empty($_COOKIE[TF_COOKIE])) {
        $data = tf_decrypt($_COOKIE[TF_COOKIE]);

        if ($data !== null) {
            unset($data['_exp']);
            $_SESSION = $data;
        }
    }

    // Buffer output so the cookie can still be set at shutdown
    ob_start();

    register_shutdown_function(function () {
        if (headers_sent()) {
            return;
        }

        if (empty($_SESSION)) {
            setcookie(TF_COOKIE, '', tf_cookie_options(time() - 3600));
            return;
        }

        $payload = $_SESSION;
        $payload['_exp'] = time() + TF_LIFETIME;

        setcookie(TF_COOKIE, tf_encrypt($payload), tf_cookie_options($payload['_exp']));
    });
}

function tf_session_destroy(): void
{
    $_SESSION = [];
}
