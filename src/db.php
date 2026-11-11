<?php

declare(strict_types=1);

/**
 * Database changes, applied in order the first time the site connects after a
 * deploy. A new empty database needs no manual setup, and an existing one is
 * upgraded. Each name is recorded in schema_migrations once it has run, so
 * never edit or rename a migration that has been deployed. Add a new one instead.
 */
const MIGRATIONS = [
    '001_initial' => [
        <<<'SQL'
        CREATE TABLE IF NOT EXISTS posts (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(190) NOT NULL,
            title VARCHAR(255) NOT NULL,
            summary VARCHAR(500) NOT NULL,
            body MEDIUMTEXT NOT NULL,
            published_on DATE NOT NULL,
            draft TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY posts_slug (slug),
            KEY posts_listing (draft, published_on)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL,
        <<<'SQL'
        CREATE TABLE IF NOT EXISTS cv_profile (
            id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            headline VARCHAR(255) NOT NULL,
            location VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            links TEXT NOT NULL,
            summary TEXT NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL,
        <<<'SQL'
        CREATE TABLE IF NOT EXISTS cv_items (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            section VARCHAR(20) NOT NULL,
            title VARCHAR(255) NOT NULL,
            subtitle VARCHAR(255) NOT NULL,
            dates VARCHAR(100) NOT NULL,
            meta VARCHAR(500) NOT NULL,
            url VARCHAR(500) NOT NULL,
            body TEXT NOT NULL,
            position INT NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY cv_items_order (section, position)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL,
        <<<'SQL'
        CREATE TABLE IF NOT EXISTS login_attempts (
            ip VARCHAR(45) NOT NULL,
            attempted_at DATETIME NOT NULL,
            KEY login_attempts_ip (ip, attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL,
        <<<'SQL'
        INSERT IGNORE INTO cv_profile (id, name, headline, location, email, links, summary)
        VALUES (1, 'Your Name', 'What you do, in a few words', '', '', '', '')
        SQL,
    ],
    '002_profile_search_fields' => [
        <<<'SQL'
        ALTER TABLE cv_profile
            ADD COLUMN search_description VARCHAR(300) NOT NULL DEFAULT '' AFTER summary,
            ADD COLUMN machine_note VARCHAR(2000) NOT NULL DEFAULT '' AFTER search_description
        SQL,
    ],
];

function db(): PDO
{
    static $pdo;
    if ($pdo === null) {
        $c = config('db');
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'] ?? 3306, $c['name']),
            $c['user'],
            $c['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
        ensure_schema($pdo);
    }

    return $pdo;
}

/** @param list<mixed> $params */
function query(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement;
}

/** Run any migrations this database hasn't had yet. Costs one small query when it's up to date. */
function ensure_schema(PDO $pdo): void
{
    try {
        $applied = $pdo->query('SELECT name FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        if ($e->getCode() !== '42S02') { // anything other than "table doesn't exist"
            throw $e;
        }
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                name VARCHAR(100) NOT NULL PRIMARY KEY,
                applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $applied = [];
    }

    foreach (MIGRATIONS as $name => $statements) {
        if (in_array($name, $applied, true)) {
            continue;
        }
        foreach ($statements as $sql) {
            $pdo->exec($sql);
        }
        $pdo->prepare('INSERT IGNORE INTO schema_migrations (name) VALUES (?)')->execute([$name]);
    }
}

/** MySQL/MariaDB error 1062: a UNIQUE column already has this value. */
function is_duplicate_key(PDOException $e): bool
{
    return ($e->errorInfo[1] ?? null) === 1062;
}
