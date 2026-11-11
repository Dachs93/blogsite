<?php
/** @var array<string, mixed> $cv */
// Any key removed from content/cv.php just hides that part of the page.
$cv += [
    'location' => '', 'email' => '', 'links' => [], 'summary' => '',
    'experience' => [], 'education' => [], 'skills' => [], 'projects' => [],
];
$contact = array_filter([
    $cv['location'] ? e($cv['location']) : null,
    $cv['email'] ? '<a href="mailto:' . e($cv['email']) . '">' . e($cv['email']) . '</a>' : null,
    ...array_map(
        fn (string $label, string $url): string => '<a href="' . e($url) . '">' . e($label) . '</a>',
        array_keys($cv['links']),
        $cv['links'],
    ),
]);
$meta = fn (string ...$parts): string => implode(' · ', array_map('e', array_filter($parts)));
?>
<article class="cv">
    <header class="cv-header">
        <h1 class="cv-name"><?= e($cv['name']) ?></h1>
        <p class="cv-headline"><?= e($cv['headline']) ?></p>
        <?php if ($contact): ?>
            <p class="cv-contact"><?= implode(' · ', $contact) ?></p>
        <?php endif ?>
    </header>

    <?php if (trim($cv['summary']) !== ''): ?>
        <section class="cv-section">
            <h2 class="cv-section-title">Summary</h2>
            <?= md($cv['summary']) ?>
        </section>
    <?php endif ?>

    <?php if ($cv['experience']): ?>
        <section class="cv-section">
            <h2 class="cv-section-title">Experience</h2>
            <?php foreach ($cv['experience'] as $job): ?>
                <div class="cv-entry">
                    <div class="cv-entry-head">
                        <h3 class="cv-entry-title"><?= e($job['role']) ?> · <?= e($job['company']) ?></h3>
                        <p class="cv-dates"><?= e($job['dates']) ?></p>
                    </div>
                    <?php if ($line = $meta($job['location'] ?? '', $job['tech'] ?? '')): ?>
                        <p class="cv-meta"><?= $line ?></p>
                    <?php endif ?>
                    <?php if (!empty($job['highlights'])): ?>
                        <ul>
                            <?php foreach ($job['highlights'] as $highlight): ?>
                                <li><?= md_inline($highlight) ?></li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </section>
    <?php endif ?>

    <?php if ($cv['education']): ?>
        <section class="cv-section">
            <h2 class="cv-section-title">Education</h2>
            <?php foreach ($cv['education'] as $course): ?>
                <div class="cv-entry">
                    <div class="cv-entry-head">
                        <h3 class="cv-entry-title"><?= e($course['qualification']) ?> · <?= e($course['institution']) ?></h3>
                        <p class="cv-dates"><?= e($course['dates']) ?></p>
                    </div>
                    <?php if (!empty($course['notes'])): ?>
                        <p class="cv-meta"><?= md_inline($course['notes']) ?></p>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </section>
    <?php endif ?>

    <?php if ($cv['skills']): ?>
        <section class="cv-section">
            <h2 class="cv-section-title">Skills</h2>
            <dl class="cv-skills">
                <?php foreach ($cv['skills'] as $group => $items): ?>
                    <div>
                        <dt><?= e($group) ?></dt>
                        <dd><?= e(implode(', ', $items)) ?></dd>
                    </div>
                <?php endforeach ?>
            </dl>
        </section>
    <?php endif ?>

    <?php if ($cv['projects']): ?>
        <section class="cv-section">
            <h2 class="cv-section-title">Projects</h2>
            <?php foreach ($cv['projects'] as $project): ?>
                <div class="cv-entry">
                    <h3 class="cv-entry-title">
                        <?php if (!empty($project['url'])): ?>
                            <a href="<?= e($project['url']) ?>"><?= e($project['name']) ?></a>
                        <?php else: ?>
                            <?= e($project['name']) ?>
                        <?php endif ?>
                    </h3>
                    <?php if (!empty($project['description'])): ?>
                        <p><?= md_inline($project['description']) ?></p>
                    <?php endif ?>
                    <?php if (!empty($project['tech'])): ?>
                        <p class="cv-meta"><?= e($project['tech']) ?></p>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </section>
    <?php endif ?>
</article>
