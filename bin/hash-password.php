<?php

/*
 * Prints a hash of a new admin password, for 'password_hash' in config.php.
 *
 *   php bin/hash-password.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$interactive = function_exists('posix_isatty') ? posix_isatty(STDIN) : stream_isatty(STDIN);

fwrite(STDERR, 'New admin password: ');
if ($interactive) {
    shell_exec('stty -echo');
}
$password = rtrim((string) fgets(STDIN), "\r\n");
if ($interactive) {
    shell_exec('stty echo');
    fwrite(STDERR, PHP_EOL);
}

if (strlen($password) < 12) {
    fwrite(STDERR, "Use at least 12 characters.\n");
    exit(1);
}

echo password_hash($password, PASSWORD_DEFAULT), PHP_EOL;
fwrite(STDERR, "Paste that line into config.php as 'password_hash'.\n");
