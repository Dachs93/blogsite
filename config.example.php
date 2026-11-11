<?php

/*
 * Copy this file to config.php (git ignores it) and fill it in.
 * config.php sits outside public/, so it's never served to visitors.
 */

return [
    // A MariaDB/MySQL database. The site creates its tables on first visit.
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'blogsite',
        'user' => 'blogsite',
        'password' => '',
    ],

    // The one account that can log in at /admin.
    'admin' => [
        'username' => 'admin',
        // Never the password itself. Generate with: php bin/hash-password.php
        'password_hash' => '',
    ],

    // A long random string that signs the login cookie. Generate with:
    //   php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
    // Changing it logs you out everywhere.
    'secret' => '',
];
