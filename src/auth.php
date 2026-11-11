<?php

declare(strict_types=1);

/*
 * Single-user login. The username and password hash live in config.php.
 *
 * Being logged in means holding a cookie "<expiry>.<signature>", signed with
 * config.php's secret. No server-side session is involved, so the host can't
 * expire it under you while you're writing. The password hash is part of what's
 * signed, so changing the password logs out every device.
 */

const AUTH_COOKIE = 'blogsite_admin';
const AUTH_LIFETIME = 14 * 24 * 3600;
const LOGIN_MAX_FAILURES = 5;
const LOGIN_WINDOW_MINUTES = 15;

function is_admin(): bool
{
    $cookie = $_COOKIE[AUTH_COOKIE] ?? null;
    if (!is_string($cookie) || !preg_match('/^(\d{1,12})\.([0-9a-f]{64})$/', $cookie, $m)) {
        return false;
    }

    return (int) $m[1] > time() && hash_equals(auth_signature($m[1]), $m[2]);
}

function check_credentials(string $username, string $password): bool
{
    $admin = config('admin');
    $hash = (string) ($admin['password_hash'] ?? '');

    // Always run password_verify, so a wrong username takes as long as a wrong password.
    $password_ok = password_verify($password, $hash !== '' ? $hash : '$2y$10$' . str_repeat('x', 53));
    $username_ok = hash_equals((string) ($admin['username'] ?? ''), $username);

    return $hash !== '' && $password_ok && $username_ok;
}

function log_in(): void
{
    $expires = time() + AUTH_LIFETIME;
    setcookie(AUTH_COOKIE, $expires . '.' . auth_signature((string) $expires), auth_cookie_options($expires));
}

function log_out(): void
{
    setcookie(AUTH_COOKIE, '', auth_cookie_options(time() - 3600));
}

/** Form token tied to the login cookie, so another site can't submit forms as you. */
function csrf_token(): string
{
    $cookie = $_COOKIE[AUTH_COOKIE] ?? '';
    return hash_hmac('sha256', 'csrf|' . (is_string($cookie) ? $cookie : ''), auth_secret());
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_ok(): bool
{
    return hash_equals(csrf_token(), input('_csrf'));
}

function login_blocked(string $ip): bool
{
    $since = date('Y-m-d H:i:s', time() - LOGIN_WINDOW_MINUTES * 60);
    $failures = (int) query('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?', [$ip, $since])->fetchColumn();

    return $failures >= LOGIN_MAX_FAILURES;
}

function record_failed_login(string $ip): void
{
    query('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)', [$ip, date('Y-m-d H:i:s')]);
    query('DELETE FROM login_attempts WHERE attempted_at < ?', [date('Y-m-d H:i:s', time() - 86400)]);
}

function clear_failed_logins(string $ip): void
{
    query('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
}

function auth_signature(string $expires): string
{
    $admin = config('admin');
    return hash_hmac('sha256', 'admin|' . $expires . '|' . ($admin['username'] ?? '') . '|' . ($admin['password_hash'] ?? ''), auth_secret());
}

function auth_secret(): string
{
    $secret = (string) config('secret');
    if (strlen($secret) < 32) {
        throw new RuntimeException("config.php's 'secret' must be a random string of at least 32 characters.");
    }
    return $secret;
}

/** @return array<string, mixed> */
function auth_cookie_options(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function is_https(): bool
{
    $https = $_SERVER['HTTPS'] ?? '';
    return ($https !== '' && strtolower((string) $https) !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}
