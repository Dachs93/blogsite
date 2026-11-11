<?php
/** @var string $active 'posts' or 'cv' */
$notice = admin_notice();
?>
<nav class="admin-nav" aria-label="Admin">
    <a href="/admin/posts"<?= $active === 'posts' ? ' aria-current="page"' : '' ?>>Posts</a>
    <a href="/admin/cv"<?= $active === 'cv' ? ' aria-current="page"' : '' ?>>CV</a>
</nav>
<?php if ($notice): ?>
    <p class="notice" role="status"><?= e($notice) ?></p>
<?php endif ?>
