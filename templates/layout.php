<?php
/**
 * @var array<string, mixed> $cv
 * @var string $content     The rendered page template.
 * @var string|null $title  Page title; the CV page leaves it unset.
 * @var string|null $description
 * @var string|null $section 'cv' or 'writing', for the current nav item.
 */
$page_title = isset($title) ? "{$title} · {$cv['name']}" : "{$cv['name']} · {$cv['headline']}";
$description = ($description ?? '') ?: $cv['headline'];
$section ??= null;
$current = fn (string $name): string => $section === $name ? ' aria-current="page"' : '';
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
<body>
    <header class="site-header">
        <div class="wrap">
            <a class="site-name" href="/"><?= e($cv['name']) ?></a>
            <nav aria-label="Main">
                <a href="/"<?= $current('cv') ?>>CV</a>
                <a href="/writing"<?= $current('writing') ?>>Writing</a>
            </nav>
        </div>
    </header>

    <main class="wrap">
<?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="wrap">© <?= date('Y') ?> <?= e($cv['name']) ?></div>
    </footer>
</body>
</html>
