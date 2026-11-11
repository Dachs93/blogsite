<?php
/**
 * @var array<string, string> $profile
 * @var string $content          The rendered page template.
 * @var string|null $title       Page title; the CV page leaves it unset.
 * @var string|null $description
 * @var string|null $section     'cv', 'writing' or 'admin', for the current nav item.
 */
$page_title = match (true) {
    $title !== null => "{$title} · {$profile['name']}",
    $profile['headline'] !== '' => "{$profile['name']} · {$profile['headline']}",
    default => $profile['name'],
};
$description = ($description ?? '') ?: $profile['headline'];
$current = fn (string $name): string => $section === $name ? ' aria-current="page"' : '';
$logged_in = is_admin();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/site.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/assets/css/print.css')) ?>" media="print">
</head>
<body<?= $section === 'admin' ? ' class="admin"' : '' ?>>
    <header class="site-header">
        <div class="wrap">
            <a class="site-name" href="/"><?= e($profile['name']) ?></a>
            <nav aria-label="Main">
                <a href="/"<?= $current('cv') ?>>CV</a>
                <a href="/writing"<?= $current('writing') ?>>Writing</a>
                <?php if ($logged_in): ?>
                    <a href="/admin"<?= $current('admin') ?>>Admin</a>
                    <form method="post" action="/admin/logout" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="link-button">Log out</button>
                    </form>
                <?php endif ?>
            </nav>
        </div>
    </header>

    <main class="wrap">
<?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="wrap">© <?= date('Y') ?> <?= e($profile['name']) ?></div>
    </footer>
</body>
</html>
