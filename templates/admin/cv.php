<?php
/**
 * @var array<string, string> $values Profile form values.
 * @var array<string, string> $errors
 * @var array<string, list<array<string, mixed>>> $items
 */
$active = 'cv';
require __DIR__ . '/_nav.php';
?>
<div class="page-head">
    <h1>CV</h1>
    <a class="button secondary" href="/">View CV</a>
</div>

<section class="admin-section" id="profile">
    <h2>Profile</h2>

    <?php if ($errors): ?>
        <p class="notice error" role="alert">Please fix the fields marked below. Nothing was saved yet.</p>
    <?php endif ?>

    <form method="post" action="/admin/cv/profile">
        <?= csrf_field() ?>
        <?= field('name', 'Name', $values['name'], $errors, 'text', '', ['required' => true, 'maxlength' => 255]) ?>
        <?= field('headline', 'Headline', $values['headline'], $errors, 'text', 'What you do, in a few words. Shown under your name.', ['maxlength' => 255]) ?>
        <div class="field-row">
            <?= field('location', 'Location', $values['location'], $errors, 'text', '', ['maxlength' => 255]) ?>
            <?= field('email', 'Email', $values['email'], $errors, 'email', '', ['maxlength' => 255]) ?>
        </div>
        <?= field('links', 'Links', $values['links'], $errors, 'textarea',
            'One per line: a label, a space, then the address. For example: GitHub https://github.com/you',
            ['rows' => 3]) ?>
        <?= field('summary', 'Summary', $values['summary'], $errors, 'textarea',
            'Two or three sentences about you. Markdown allowed.',
            ['rows' => 5]) ?>

        <h3 class="form-subhead">Search engines</h3>
        <?= field('search_description', 'Search description', $values['search_description'], $errors, 'textarea',
            'Suggested text for the snippet under your link in Google. Google may use it or write its own. About 155 characters shows in full. Blank uses your headline.',
            ['rows' => 2, 'maxlength' => 300]) ?>
        <?= field('machine_note', 'Hidden note for machines', $values['machine_note'], $errors, 'textarea',
            'Not shown to visitors, screen readers or in print, but it\'s in the page source, so crawlers and AI summaries read it (and so can anyone who views the source). Plain text. Leave blank for none.',
            ['rows' => 3, 'maxlength' => 2000]) ?>

        <div class="actions">
            <button type="submit" class="button">Save profile</button>
        </div>
    </form>
</section>

<?php foreach (CV_SECTIONS as $key => $def): ?>
    <section class="admin-section" id="<?= e($key) ?>">
        <div class="page-head">
            <h2><?= e($def['heading']) ?></h2>
            <?php if (empty($def['single']) || !$items[$key]): ?>
                <a class="button secondary" href="/admin/cv/items/new?section=<?= e($key) ?>">Add <?= e($def['item']) ?></a>
            <?php endif ?>
        </div>

        <?php if ($items[$key]): ?>
            <ol class="item-list">
                <?php $last = count($items[$key]) - 1; ?>
                <?php foreach ($items[$key] as $i => $item): ?>
                    <li>
                        <div class="item-label">
                            <a href="/admin/cv/items/<?= (int) $item['id'] ?>/edit"><?= e(cv_item_label($item)) ?></a>
                            <?php if ($item['subtitle'] !== ''): ?><span class="muted"> · <?= e($item['subtitle']) ?></span><?php endif ?>
                            <?php if ($item['dates'] !== ''): ?><span class="muted"> · <?= e($item['dates']) ?></span><?php endif ?>
                        </div>
                        <div class="item-actions">
                            <?php foreach (['up' => '↑', 'down' => '↓'] as $direction => $arrow): ?>
                                <form method="post" action="/admin/cv/items/<?= (int) $item['id'] ?>/move" class="inline-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="direction" value="<?= $direction ?>">
                                    <button type="submit" class="icon-button"
                                        aria-label="Move <?= e(cv_item_label($item)) ?> <?= $direction ?>"
                                        <?= ($direction === 'up' && $i === 0) || ($direction === 'down' && $i === $last) ? 'disabled' : '' ?>><?= $arrow ?></button>
                                </form>
                            <?php endforeach ?>
                        </div>
                    </li>
                <?php endforeach ?>
            </ol>
        <?php else: ?>
            <p class="muted">Nothing here yet. This section stays hidden on the CV until you add something.</p>
        <?php endif ?>
    </section>
<?php endforeach ?>
