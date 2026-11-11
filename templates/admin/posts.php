<?php
/** @var list<Post> $posts */
$active = 'posts';
require __DIR__ . '/_nav.php';
?>
<div class="page-head">
    <h1>Posts</h1>
    <a class="button" href="/admin/posts/new">New post</a>
</div>

<?php if ($posts): ?>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th scope="col">Title</th><th scope="col">Status</th><th scope="col">Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><a href="/admin/posts/<?= $post->id ?>/edit"><?= e($post->title) ?></a></td>
                        <td><span class="badge status-<?= strtolower($post->status()) ?>"><?= e($post->status()) ?></span></td>
                        <td><time datetime="<?= $post->date->format('Y-m-d') ?>"><?= $post->date->format('j M Y') ?></time></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>No posts yet. <a href="/admin/posts/new">Write the first one.</a></p>
<?php endif ?>
