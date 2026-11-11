<?php

declare(strict_types=1);

/** Messages shown after a redirect, keyed by the ?notice= value. */
const ADMIN_NOTICES = [
    'saved' => 'Saved.',
    'deleted' => 'Deleted.',
    'loggedout' => 'You\'ve logged out.',
];

function admin_route(string $method, string $path): void
{
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');

    if ($path === '/admin/login') {
        if (is_admin()) {
            redirect('/admin');
        } elseif ($method === 'POST') {
            admin_login();
        } else {
            render('admin/login', admin_vars('Log in'));
        }
        return;
    }

    if (!is_admin()) {
        redirect('/admin/login');
        return;
    }

    if ($method === 'POST' && !csrf_ok()) {
        render('admin/message', admin_vars('Form expired') + [
            'message' => 'That form had expired. Go back, reload the page and try again.',
        ], 400);
        return;
    }

    // "/admin/posts/12/edit" becomes route "GET /admin/posts/:id/edit" with $id = 12.
    $id = null;
    $route = "{$method} {$path}";
    if (preg_match('#^(/admin/(?:posts|cv/items))/(\d{1,10})(/[a-z]+)?$#', $path, $m)) {
        $id = (int) $m[2];
        $route = "{$method} {$m[1]}/:id" . ($m[3] ?? '');
    }

    match ($route) {
        'GET /admin' => redirect('/admin/posts'),
        'GET /admin/posts' => render('admin/posts', admin_vars('Posts') + ['posts' => all_posts(include_hidden: true)]),
        'GET /admin/posts/new' => admin_post_form(null),
        'POST /admin/posts' => admin_post_save(null),
        'GET /admin/posts/:id/edit' => admin_post_form($id),
        'POST /admin/posts/:id' => admin_post_save($id),
        'POST /admin/posts/:id/delete' => admin_post_delete($id),
        'GET /admin/cv' => admin_cv(cv_profile(), []),
        'POST /admin/cv/profile' => admin_profile_save(),
        'GET /admin/cv/items/new' => admin_cv_item_form(null),
        'POST /admin/cv/items' => admin_cv_item_save(null),
        'GET /admin/cv/items/:id/edit' => admin_cv_item_form($id),
        'POST /admin/cv/items/:id' => admin_cv_item_save($id),
        'POST /admin/cv/items/:id/delete' => admin_cv_item_delete($id),
        'POST /admin/cv/items/:id/move' => admin_cv_item_move($id),
        'POST /admin/logout' => admin_logout(),
        default => not_found(),
    };
}

/** @return array<string, mixed> */
function admin_vars(string $title): array
{
    return ['title' => $title, 'section' => 'admin'];
}

function admin_notice(): ?string
{
    $key = $_GET['notice'] ?? null;
    return is_string($key) ? (ADMIN_NOTICES[$key] ?? null) : null;
}

// --- Logging in and out ---

function admin_login(): void
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $username = trim(input('username'));
    $vars = admin_vars('Log in') + ['username' => $username];

    if (login_blocked($ip)) {
        render('admin/login', $vars + ['error' => 'Too many failed attempts. Try again in ' . LOGIN_WINDOW_MINUTES . ' minutes.'], 429);
        return;
    }

    if (check_credentials($username, input('password'))) {
        clear_failed_logins($ip);
        log_in();
        redirect('/admin');
        return;
    }

    record_failed_login($ip);
    render('admin/login', $vars + ['error' => 'Wrong username or password.'], 401);
}

function admin_logout(): void
{
    log_out();
    redirect('/admin/login?notice=loggedout');
}

// --- Posts ---

function admin_post_form(?int $id): void
{
    $post = $id === null ? null : find_post($id);
    if ($id !== null && !$post) {
        not_found();
        return;
    }

    render('admin/post-form', admin_vars($post ? 'Edit post' : 'New post') + [
        'post' => $post,
        'values' => post_form_values($post),
        'errors' => [],
    ]);
}

function admin_post_save(?int $id): void
{
    $post = $id === null ? null : find_post($id);
    if ($id !== null && !$post) {
        not_found();
        return;
    }

    [$values, $errors] = post_input();
    if (!$errors) {
        $saved_id = save_post($values, $id);
        if ($saved_id !== null) {
            redirect("/admin/posts/{$saved_id}/edit?notice=saved");
            return;
        }
        $errors['slug'] = 'Another post already uses this address. Choose a different one.';
    }

    render('admin/post-form', admin_vars($post ? 'Edit post' : 'New post') + [
        'post' => $post,
        'values' => $values,
        'errors' => $errors,
    ], 422);
}

function admin_post_delete(int $id): void
{
    delete_post($id);
    redirect('/admin/posts?notice=deleted');
}

// --- CV ---

/**
 * @param array<string, string> $values profile form values
 * @param array<string, string> $errors
 */
function admin_cv(array $values, array $errors, int $status = 200): void
{
    render('admin/cv', admin_vars('CV') + [
        'values' => $values,
        'errors' => $errors,
        'items' => cv_items_by_section(),
    ], $status);
}

function admin_profile_save(): void
{
    [$values, $errors] = profile_input();
    if ($errors) {
        admin_cv($values, $errors, 422);
        return;
    }

    save_profile($values);
    redirect('/admin/cv?notice=saved');
}

function admin_cv_item_form(?int $id): void
{
    $item = $id === null ? null : find_cv_item($id);
    $section = $item['section'] ?? (string) ($_GET['section'] ?? '');
    if (($id !== null && !$item) || !isset(CV_SECTIONS[$section])) {
        not_found();
        return;
    }

    admin_render_cv_item_form($section, $item, cv_item_form_values($item), []);
}

function admin_cv_item_save(?int $id): void
{
    $item = $id === null ? null : find_cv_item($id);
    $section = $item['section'] ?? input('section');
    if (($id !== null && !$item) || !isset(CV_SECTIONS[$section])) {
        not_found();
        return;
    }

    [$values, $errors] = cv_item_input($section);
    if ($errors) {
        admin_render_cv_item_form($section, $item, $values, $errors, 422);
        return;
    }

    save_cv_item($section, $values, $id);
    redirect("/admin/cv?notice=saved#{$section}");
}

function admin_cv_item_delete(int $id): void
{
    $section = find_cv_item($id)['section'] ?? '';
    delete_cv_item($id);
    redirect("/admin/cv?notice=deleted#{$section}");
}

function admin_cv_item_move(int $id): void
{
    $section = find_cv_item($id)['section'] ?? '';
    move_cv_item($id, input('direction'));
    redirect("/admin/cv#{$section}");
}

/**
 * @param array<string, mixed>|null $item
 * @param array<string, string> $values
 * @param array<string, string> $errors
 */
function admin_render_cv_item_form(string $section, ?array $item, array $values, array $errors, int $status = 200): void
{
    $def = CV_SECTIONS[$section];
    render('admin/cv-item-form', admin_vars(($item ? 'Edit ' : 'Add ') . $def['item']) + [
        'cv_section' => $section,
        'def' => $def,
        'item' => $item,
        'values' => $values,
        'errors' => $errors,
    ], $status);
}

// --- Form helpers for the admin templates ---

/**
 * A labelled input or textarea, with an optional hint and its validation error.
 *
 * @param array<string, string> $errors
 * @param array<string, scalar|bool|null> $attrs extra attributes, e.g. ['required' => true, 'rows' => 4]
 */
function field(string $name, string $label, string $value, array $errors, string $type = 'text', string $hint = '', array $attrs = []): string
{
    $id = 'f-' . $name;
    $after = '';
    $described_by = [];

    if ($hint !== '') {
        $described_by[] = "{$id}-hint";
        $after .= '<p class="hint" id="' . $id . '-hint">' . e($hint) . '</p>';
    }
    if (isset($errors[$name])) {
        $described_by[] = "{$id}-error";
        $after .= '<p class="field-error" id="' . $id . '-error">' . e($errors[$name]) . '</p>';
        $attrs['aria-invalid'] = 'true';
    }
    if ($described_by) {
        $attrs['aria-describedby'] = implode(' ', $described_by);
    }

    $attr_html = '';
    foreach ($attrs as $key => $attr) {
        if ($attr === true) {
            $attr_html .= ' ' . e($key);
        } elseif ($attr !== false && $attr !== null) {
            $attr_html .= ' ' . e($key) . '="' . e((string) $attr) . '"';
        }
    }

    // The newline after <textarea> is dropped by browsers, so a value starting with one survives.
    $control = $type === 'textarea'
        ? '<textarea id="' . $id . '" name="' . e($name) . '"' . $attr_html . ">\n" . e($value) . '</textarea>'
        : '<input type="' . e($type) . '" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"' . $attr_html . '>';

    return '<div class="field' . (isset($errors[$name]) ? ' has-error' : '') . '"><label for="' . $id . '">' . e($label) . '</label>' . $control . $after . '</div>';
}
