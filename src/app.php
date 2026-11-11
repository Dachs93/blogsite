<?php

declare(strict_types=1);

define('ROOT', dirname(__DIR__));

/** True when running under `php -S` locally: show errors and draft posts. */
define('DEV', PHP_SAPI === 'cli-server');

ini_set('display_errors', DEV ? '1' : '0');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Dublin');

require ROOT . '/lib/Parsedown.php';
require ROOT . '/src/posts.php';

function run(string $path): void
{
    send_security_headers();

    if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
        header('Allow: GET, HEAD');
        render('404', ['title' => 'Not allowed'], 405);
        return;
    }

    // One canonical URL per page: /writing/, not /writing.
    if ($path !== '/' && str_ends_with($path, '/')) {
        redirect(rtrim($path, '/'));
        return;
    }

    if ($path === '/') {
        render('cv', ['section' => 'cv']);
    } elseif ($path === '/writing') {
        render('posts', ['section' => 'writing', 'title' => 'Writing', 'posts' => all_posts()]);
    } elseif (preg_match('#^/writing/([a-z0-9-]+)$#', $path, $m) && ($post = find_post($m[1]))) {
        render('post', [
            'section' => 'writing',
            'title' => $post->title,
            'description' => $post->summary,
            'post' => $post,
        ]);
    } else {
        render('404', ['title' => 'Page not found'], 404);
    }
}

/**
 * Render templates/{$template}.php inside templates/layout.php.
 *
 * @param array<string, mixed> $vars
 */
function render(string $template, array $vars = [], int $status = 200): void
{
    http_response_code($status);
    $vars['cv'] = cv();

    $content = (static function (string $__template, array $__vars): string {
        extract($__vars);
        ob_start();
        require ROOT . "/templates/{$__template}.php";
        return (string) ob_get_clean();
    })($template, $vars);

    extract($vars);
    require ROOT . '/templates/layout.php';
}

function redirect(string $to, int $status = 301): void
{
    header('Location: ' . $to, true, $status);
}

function send_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' https: data:; script-src 'none'; object-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");
}

/** @return array<string, mixed> The CV data from content/cv.php. */
function cv(): array
{
    static $cv;
    return $cv ??= require ROOT . '/content/cv.php';
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

/** Markdown block (paragraphs, lists, code) to HTML. Content is your own, so HTML in it is allowed. */
function md(string $text): string
{
    return markdown()->text($text);
}

/** Inline Markdown (links, bold, code) to HTML, without wrapping <p>. */
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
