<?php
/** @var list<Post> $posts */
?>
<h1>Writing</h1>

<?php if ($posts): ?>
    <ul class="post-list">
        <?php foreach ($posts as $post): ?>
            <li>
                <h2><a href="<?= e($post->url()) ?>"><?= e($post->title) ?></a></h2>
                <p class="post-meta">
                    <time datetime="<?= $post->date->format('Y-m-d') ?>"><?= $post->date->format('j F Y') ?></time>
                    <?php if (!$post->isPublished()): ?><span class="badge">Draft (local only)</span><?php endif ?>
                </p>
                <?php if ($post->summary): ?>
                    <p><?= e($post->summary) ?></p>
                <?php endif ?>
            </li>
        <?php endforeach ?>
    </ul>
<?php else: ?>
    <p>Nothing here yet.</p>
<?php endif ?>
