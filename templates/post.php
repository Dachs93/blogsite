<?php
/** @var Post $post */
?>
<article class="post">
    <header>
        <h1><?= e($post->title) ?></h1>
        <p class="post-meta">
            <time datetime="<?= $post->date->format('Y-m-d') ?>"><?= $post->date->format('j F Y') ?></time>
            <?php if (!$post->isPublished()): ?><span class="badge">Draft (local only)</span><?php endif ?>
        </p>
    </header>

    <div class="prose">
        <?= $post->html() ?>
    </div>
</article>

<p class="back"><a href="/writing">← All writing</a></p>
