<?php
/**
 * @var array<string, string> $profile
 * @var array<string, list<array<string, mixed>>> $items
 */
$contact = array_filter([
    $profile['location'] !== '' ? e($profile['location']) : null,
    $profile['email'] !== '' ? '<a href="mailto:' . e($profile['email']) . '">' . e($profile['email']) . '</a>' : null,
    ...array_map(
        fn (array $link): string => '<a href="' . e($link['url']) . '">' . e($link['label']) . '</a>',
        cv_links($profile['links']),
    ),
]);
?>
<article class="cv">
    <header class="cv-header">
        <h1 class="cv-name"><?= e($profile['name']) ?></h1>
        <?php if ($profile['headline'] !== ''): ?>
            <p class="cv-headline"><?= e($profile['headline']) ?></p>
        <?php endif ?>
        <?php if ($contact): ?>
            <p class="cv-contact"><?= implode(' · ', $contact) ?></p>
        <?php endif ?>
        <?php if ($profile['machine_note'] !== ''): ?>
            <p class="machine-note" aria-hidden="true"><?= nl2br(e($profile['machine_note'])) ?></p>
        <?php endif ?>
    </header>

    <?php if (trim($profile['summary']) !== ''): ?>
        <section class="cv-section">
            <h2 class="cv-section-title">Summary</h2>
            <?= md($profile['summary']) ?>
        </section>
    <?php endif ?>

    <?php foreach (CV_SECTIONS as $key => $def): ?>
        <?php if (!$items[$key]) continue; ?>
        <section class="cv-section">
            <h2 class="cv-section-title"><?= e($def['heading']) ?></h2>

            <?php if ($key === 'skill'): ?>
                <dl class="cv-skills">
                    <?php foreach ($items[$key] as $item): ?>
                        <div>
                            <dt><?= e($item['title']) ?></dt>
                            <dd><?= e($item['body']) ?></dd>
                        </div>
                    <?php endforeach ?>
                </dl>
            <?php else: ?>
                <?php foreach ($items[$key] as $item): ?>
                    <div class="cv-entry">
                        <div class="cv-entry-head">
                            <h3 class="cv-entry-title">
                                <?php if ($item['url'] !== ''): ?>
                                    <a href="<?= e($item['url']) ?>"><?= e($item['title']) ?></a>
                                <?php else: ?>
                                    <?= e($item['title']) ?>
                                <?php endif ?>
                                <?php if ($item['subtitle'] !== ''): ?>
                                    · <?= e($item['subtitle']) ?>
                                <?php endif ?>
                            </h3>
                            <?php if ($item['dates'] !== ''): ?>
                                <p class="cv-dates"><?= e($item['dates']) ?></p>
                            <?php endif ?>
                        </div>
                        <?php if ($key === 'project'): ?>
                            <?php if ($item['body'] !== ''): ?><?= md($item['body']) ?><?php endif ?>
                            <?php if ($item['meta'] !== ''): ?><p class="cv-meta"><?= md_inline($item['meta']) ?></p><?php endif ?>
                        <?php else: ?>
                            <?php if ($item['meta'] !== ''): ?><p class="cv-meta"><?= md_inline($item['meta']) ?></p><?php endif ?>
                            <?php if ($item['body'] !== ''): ?><?= md($item['body']) ?><?php endif ?>
                        <?php endif ?>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </section>
    <?php endforeach ?>
</article>
