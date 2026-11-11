<?php
/**
 * @var Post|null $post
 * @var array<string, string> $values
 * @var array<string, string> $errors
 */
$active = 'posts';
require __DIR__ . '/_nav.php';
?>
<h1><?= $post ? 'Edit post' : 'New post' ?></h1>

<?php if ($errors): ?>
    <p class="notice error" role="alert">Please fix the fields marked below. Nothing was saved yet.</p>
<?php endif ?>

<form method="post" action="<?= $post ? '/admin/posts/' . $post->id : '/admin/posts' ?>">
    <?= csrf_field() ?>

    <?= field('title', 'Title', $values['title'], $errors, 'text', '', ['required' => true, 'maxlength' => 255]) ?>

    <?= field('slug', 'Address', $values['slug'], $errors, 'text',
        'The end of the post\'s address: /writing/this-part. Leave it blank to make one from the title. Changing it later breaks existing links.',
        ['maxlength' => 190, 'autocapitalize' => 'none', 'spellcheck' => 'false']) ?>

    <?= field('summary', 'Summary', $values['summary'], $errors, 'textarea',
        'One sentence, shown in the list of posts and in search results.',
        ['rows' => 2, 'maxlength' => 500]) ?>

    <?= field('body', 'Post', $values['body'], $errors, 'textarea',
        'Markdown: **bold**, *italic*, [link text](https://…), "## " for a heading, "- " for a bullet, and ``` around code.',
        ['rows' => 22, 'class' => 'code']) ?>

    <div class="field-row">
        <?= field('published_on', 'Date', $values['published_on'], $errors, 'date', '', ['required' => true]) ?>
        <div class="field">
            <label for="f-status">Status</label>
            <select id="f-status" name="status" aria-describedby="f-status-hint">
                <option value="draft"<?= $values['status'] === 'draft' ? ' selected' : '' ?>>Draft: only you can see it</option>
                <option value="published"<?= $values['status'] === 'published' ? ' selected' : '' ?>>Published</option>
            </select>
            <p class="hint" id="f-status-hint">A published post with a future date goes live on that day.</p>
        </div>
    </div>

    <div class="actions">
        <button type="submit" class="button">Save</button>
        <?php if ($post): ?>
            <a class="button secondary" href="<?= e($post->url()) ?>">View</a>
        <?php endif ?>
        <a href="/admin/posts">Back to posts</a>
    </div>
</form>

<?php if ($post): ?>
    <details class="danger-zone">
        <summary>Delete this post</summary>
        <form method="post" action="/admin/posts/<?= $post->id ?>/delete">
            <?= csrf_field() ?>
            <p>"<?= e($post->title) ?>" will be removed for good. This can't be undone.</p>
            <button type="submit" class="button danger">Delete post</button>
        </form>
    </details>
<?php endif ?>
