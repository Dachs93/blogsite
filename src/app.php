<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

/** True when running under `php -S` locally: PHP errors are shown on the page. */
define('DEV', PHP_SAPI === 'cli-server');

ini_set('display_errors', DEV ? '1' : '0');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Dublin');

require ROOT . '/lib/Parsedown.php';
require ROOT . '/src/db.php';
require ROOT . '/src/auth.php';
require ROOT . '/src/posts.php';
require ROOT . '/src/cv.php';
require ROOT . '/src/admin.php';

function run(string $method, string $path): void
{
    if (!DEV) {
        set_exception_handler('show_server_error');
    }

    send_security_headers();
    $method = $method === 'HEAD' ? 'GET' : $method;

    // One canonical URL per page: /writing, not /writing/.
    if ($method === 'GET' && $path !== '/' && str_ends_with($path, '/')) {
        redirect(rtrim($path, '/'), 301);
        return;
    }

    if ($path === '/admin' || str_starts_with($path, '/admin/')) {
        admin_route($method, $path);
        return;
    }

    if ($method !== 'GET') {
        header('Allow: GET, HEAD');
        render('404', ['title' => 'Not allowed'], 405);
        return;
    }

    // When you're logged in, public pages also show drafts, so they mustn't be cached.
    if (is_admin()) {
        header('Cache-Control: no-store');
    }

    if ($path === '/') {
        render('cv', ['section' => 'cv', 'items' => cv_items_by_section()]);
    } elseif ($path === '/writing') {
        render('posts', ['section' => 'writing', 'title' => 'Writing', 'posts' => all_posts(include_hidden: is_admin())]);
    } elseif (preg_match('#^/writing/([a-z0-9-]+)$#', $path, $m) && ($post = find_post_by_slug($m[1], include_hidden: is_admin()))) {
        render('post', [
            'section' => 'writing',
            'title' => $post->title,
            'description' => $post->summary,
            'post' => $post,
        ]);
    } else {
        not_found();
    }
}

/**
 * Render templates/{$template}.php inside templates/layout.php.
 *
 * @param array<string, mixed> $vars
 */
function render(string $template, array $vars = [], int $status = 200): void
{
    $vars += ['profile' => cv_profile(), 'title' => null, 'description' => null, 'section' => null];

    $content = (static function (string $__template, array $__vars): string {
        extract($__vars);
        ob_start();
        try {
            require ROOT . "/templates/{$__template}.php";
            return (string) ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    })($template, $vars);

    http_response_code($status);
    extract($vars);
    require ROOT . '/templates/layout.php';
}

function not_found(): void
{
    render('404', ['title' => 'Page not found'], 404);
}

/** Redirect; 303 by default so a form POST is followed by a GET. */
function redirect(string $to, int $status = 303): void
{
    header('Location: ' . $to, true, $status);
}

/** Live site only: log the real error and show a plain page that needs neither templates nor the database. */
function show_server_error(Throwable $e): void
{
    error_log((string) $e);
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Something went wrong</title>',
        '<p style="font-family:system-ui,sans-serif;max-width:30rem;margin:4rem auto;padding:0 1rem">',
        'Something went wrong on this site. Please try again in a minute.</p></html>';
}

function send_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' https: data:; script-src 'none'; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
}

/** A setting from config.php (git ignores it; config.example.php shows the format). */
function config(string $key): mixed
{
    static $config;
    if ($config === null) {
        $file = ROOT . '/config.php';
        if (!is_file($file)) {
            throw new RuntimeException('config.php is missing. Copy config.example.php to config.php and fill it in.');
        }
        $config = require $file;
    }

    return $config[$key] ?? throw new RuntimeException("config.php has no '{$key}' setting.");
}

/** A submitted form value as a string ('' if missing or not a string). */
function input(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? $value : '';
}

/** Escape text for HTML. */
function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function markdown(): Parsedown
{
    static $parser;
    return $parser ??= new Parsedown();
}

/** Markdown block (paragraphs, lists, code) to HTML. Only you can write content, so HTML in it is allowed. */
function md(string $text): string
{
    return markdown()->text($text);
}

/** Inline Markdown (links, bold, code) to HTML, without a wrapping <p>. */
function md_inline(string $text): string
{
    return markdown()->line($text);
}

/** URL for a file in public/, with a version that changes whenever the file does. */
function asset(string $path): string
{
    $mtime = @filemtime(ROOT . '/public' . $path);
    return $path . ($mtime ? '?v=' . $mtime : '');
}
