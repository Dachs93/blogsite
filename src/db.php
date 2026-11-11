<?php

declare(strict_types=1);

/**
 * Tables are created automatically the first time the site connects, so a new
 * empty database needs no manual setup. Statements only ever add what's missing.
 */
const SCHEMA = [
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
];

const SCHEMA_TABLES = ['posts', 'cv_profile', 'cv_items', 'login_attempts'];

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

function ensure_schema(PDO $pdo): void
{
    $existing = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    if (array_diff(SCHEMA_TABLES, $existing) === []) {
        return;
    }

    foreach (SCHEMA as $sql) {
        $pdo->exec($sql);
    }
}

/** MySQL/MariaDB error 1062: a UNIQUE column already has this value. */
function is_duplicate_key(PDOException $e): bool
{
    return ($e->errorInfo[1] ?? null) === 1062;
}
