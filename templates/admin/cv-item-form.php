<?php
/**
 * @var string $cv_section
 * @var array{heading: string, item: string, fields: array<string, string>, hints: array<string, string>} $def
 * @var array<string, mixed>|null $item
 * @var array<string, string> $values
 * @var array<string, string> $errors
 */
$active = 'cv';
require __DIR__ . '/_nav.php';

$types = ['url' => 'url', 'body' => 'textarea'];
$attrs = [
    'title' => ['required' => true, 'maxlength' => 255],
    'subtitle' => ['maxlength' => 255],
    'dates' => ['maxlength' => 100],
    'meta' => ['maxlength' => 500],
    'url' => ['maxlength' => 500],
    'body' => ['rows' => $cv_section === 'experience' ? 6 : 3],
];
?>
<p class="back-link"><a href="/admin/cv#<?= e($cv_section) ?>">← <?= e($def['heading']) ?></a></p>
<h1><?= $item ? 'Edit ' . e($def['item']) : 'Add ' . e($def['item']) ?></h1>

<?php if ($errors): ?>
    <p class="notice error" role="alert">Please fix the fields marked below. Nothing was saved yet.</p>
<?php endif ?>

<form method="post" action="<?= $item ? '/admin/cv/items/' . (int) $item['id'] : '/admin/cv/items' ?>">
    <?= csrf_field() ?>
    <?php if (!$item): ?>
        <input type="hidden" name="section" value="<?= e($cv_section) ?>">
    <?php endif ?>

    <?php foreach ($def['fields'] as $name => $label): ?>
        <?= field($name, $label, $values[$name], $errors, $types[$name] ?? 'text', $def['hints'][$name] ?? '', $attrs[$name]) ?>
    <?php endforeach ?>

    <div class="actions">
        <button type="submit" class="button">Save</button>
        <a href="/admin/cv#<?= e($cv_section) ?>">Cancel</a>
    </div>
</form>

<?php if ($item): ?>
    <details class="danger-zone">
        <summary>Delete this <?= e($def['item']) ?></summary>
        <form method="post" action="/admin/cv/items/<?= (int) $item['id'] ?>/delete">
            <?= csrf_field() ?>
            <p>"<?= e($item['title']) ?>" will be removed from your CV. This can't be undone.</p>
            <button type="submit" class="button danger">Delete</button>
        </form>
    </details>
<?php endif ?>
