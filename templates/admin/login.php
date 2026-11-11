<?php
/**
 * @var string|null $error
 * @var string|null $username
 */
$notice = admin_notice();
?>
<div class="narrow">
    <h1>Log in</h1>

    <?php if (!empty($error)): ?>
        <p class="notice error" role="alert"><?= e($error) ?></p>
    <?php elseif ($notice): ?>
        <p class="notice" role="status"><?= e($notice) ?></p>
    <?php endif ?>

    <form method="post" action="/admin/login">
        <?= field('username', 'Username', $username ?? '', [], 'text', '', ['required' => true, 'autofocus' => true, 'autocomplete' => 'username', 'autocapitalize' => 'none']) ?>
        <?= field('password', 'Password', '', [], 'password', '', ['required' => true, 'autocomplete' => 'current-password']) ?>
        <div class="actions">
            <button type="submit" class="button">Log in</button>
        </div>
    </form>
</div>
