<?php

declare(strict_types=1);

/*
 * The CV: one profile (name, contact, summary) plus entries in sections.
 * Every entry is a row in cv_items. Each section uses only some of the columns,
 * and the labels below are what the admin form calls them.
 */
const CV_SECTIONS = [
    'experience' => [
        'heading' => 'Experience',
        'item' => 'job',
        'fields' => ['title' => 'Role', 'subtitle' => 'Company', 'dates' => 'Dates', 'meta' => 'Location and tools', 'body' => 'Highlights'],
        'hints' => [
            'dates' => 'For example: 2022 – Present',
            'meta' => 'For example: Dublin · PHP, MariaDB',
            'body' => 'Markdown. One bullet per line, starting with "- ".',
        ],
    ],
    'education' => [
        'heading' => 'Education',
        'item' => 'qualification',
        'fields' => ['title' => 'Qualification', 'subtitle' => 'Institution', 'dates' => 'Dates', 'meta' => 'Notes'],
        'hints' => ['meta' => 'Grade, thesis or focus. Optional.'],
    ],
    'skill' => [
        'heading' => 'Skills',
        'item' => 'skills',
        'single' => true, // One box for the whole section, not a list of entries.
        'fields' => ['body' => 'Skills'],
        'hints' => ['body' => 'Markdown. One bullet per line, starting with "- ".'],
    ],
    'project' => [
        'heading' => 'Projects',
        'item' => 'project',
        'fields' => ['title' => 'Name', 'url' => 'Link', 'body' => 'Description', 'meta' => 'Built with'],
        'hints' => ['url' => 'Optional. A full address starting with https://', 'body' => 'Markdown. A sentence or two.'],
    ],
];

const CV_ITEM_FIELDS = ['title', 'subtitle', 'dates', 'meta', 'url', 'body'];

/** @return array<string, string> */
function cv_profile(): array
{
    static $profile;
    return $profile ??= query('SELECT * FROM cv_profile WHERE id = 1')->fetch() ?: [
        'name' => 'Your Name', 'headline' => '', 'location' => '', 'email' => '', 'links' => '', 'summary' => '',
        'search_description' => '', 'machine_note' => '',
    ];
}

/**
 * The profile's links field: one per line, a label then the address.
 *
 * @return list<array{label: string, url: string}>
 */
function cv_links(string $text): array
{
    $links = [];
    foreach (preg_split('/\R/', $text) ?: [] as $line) {
        if (preg_match('#^\s*(.+?)\s+((?:https?://|mailto:)\S+)\s*$#i', $line, $m)) {
            $links[] = ['label' => $m[1], 'url' => $m[2]];
        }
    }
    return $links;
}

/** @return array{0: array<string, string>, 1: array<string, string>} [values, errors by field] */
function profile_input(): array
{
    $values = [
        'name' => trim(input('name')),
        'headline' => trim(input('headline')),
        'location' => trim(input('location')),
        'email' => trim(input('email')),
        'links' => trim(str_replace("\r\n", "\n", input('links'))),
        'summary' => trim(str_replace("\r\n", "\n", input('summary'))),
        'search_description' => trim(preg_replace('/\s+/', ' ', input('search_description')) ?? ''),
        'machine_note' => trim(str_replace("\r\n", "\n", input('machine_note'))),
    ];

    $errors = [];
    if (mb_strlen($values['search_description']) > 300) {
        $errors['search_description'] = 'Keep this under 300 characters. Google shows about 155.';
    }
    if (mb_strlen($values['machine_note']) > 2000) {
        $errors['machine_note'] = 'Keep this under 2,000 characters.';
    }
    if ($values['name'] === '') {
        $errors['name'] = 'Your name is shown at the top of every page.';
    }
    foreach (['name', 'headline', 'location', 'email'] as $field) {
        if (!isset($errors[$field]) && mb_strlen($values[$field]) > 255) {
            $errors[$field] = 'Keep this under 255 characters.';
        }
    }
    if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'That doesn\'t look like an email address.';
    }
    foreach (preg_split('/\R/', $values['links']) ?: [] as $line) {
        if (trim($line) !== '' && !cv_links($line)) {
            $errors['links'] = 'This line needs a label and then an address starting with https://: "' . trim($line) . '"';
            break;
        }
    }

    return [$values, $errors];
}

/** @param array<string, string> $values */
function save_profile(array $values): void
{
    query(
        'INSERT INTO cv_profile (id, name, headline, location, email, links, summary, search_description, machine_note)
         VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE name = VALUES(name), headline = VALUES(headline), location = VALUES(location),
             email = VALUES(email), links = VALUES(links), summary = VALUES(summary),
             search_description = VALUES(search_description), machine_note = VALUES(machine_note)',
        [
            $values['name'], $values['headline'], $values['location'], $values['email'], $values['links'],
            $values['summary'], $values['search_description'], $values['machine_note'],
        ],
    );
}

/** @return array<string, list<array<string, mixed>>> Entries grouped by section, in display order. */
function cv_items_by_section(): array
{
    $grouped = array_fill_keys(array_keys(CV_SECTIONS), []);
    foreach (query('SELECT * FROM cv_items ORDER BY position, id')->fetchAll() as $row) {
        if (isset($grouped[$row['section']])) {
            $grouped[$row['section']][] = $row;
        }
    }
    return $grouped;
}

/**
 * What the admin calls an entry: its title, or the section heading for a section without titles.
 *
 * @param array<string, mixed> $item
 */
function cv_item_label(array $item): string
{
    return $item['title'] !== '' ? $item['title'] : (CV_SECTIONS[$item['section']]['heading'] ?? 'Entry');
}

/** @return array<string, mixed>|null */
function find_cv_item(int $id): ?array
{
    return query('SELECT * FROM cv_items WHERE id = ?', [$id])->fetch() ?: null;
}

/**
 * @param array<string, mixed>|null $item
 * @return array<string, string>
 */
function cv_item_form_values(?array $item): array
{
    $values = [];
    foreach (CV_ITEM_FIELDS as $field) {
        $values[$field] = (string) ($item[$field] ?? '');
    }
    return $values;
}

/** @return array{0: array<string, string>, 1: array<string, string>} [values, errors by field] */
function cv_item_input(string $section): array
{
    $used = CV_SECTIONS[$section]['fields'];
    $values = [];
    foreach (CV_ITEM_FIELDS as $field) {
        // Fields this section doesn't use are always stored empty.
        $values[$field] = isset($used[$field]) ? trim(str_replace("\r\n", "\n", input($field))) : '';
    }

    $limits = ['title' => 255, 'subtitle' => 255, 'dates' => 100, 'meta' => 500, 'url' => 500];
    $errors = [];
    $required = array_key_first($used);
    if ($values[$required] === '') {
        $errors[$required] = 'This one is required.';
    }
    foreach ($limits as $field => $max) {
        if (!isset($errors[$field]) && mb_strlen($values[$field]) > $max) {
            $errors[$field] = "Keep this under {$max} characters.";
        }
    }
    if ($values['url'] !== '' && !preg_match('#^https?://\S+$#i', $values['url'])) {
        $errors['url'] = 'Use a full address starting with https://';
    }

    return [$values, $errors];
}

/**
 * Insert (at the end of its section) or update an entry.
 *
 * @param array<string, string> $values from cv_item_input()
 */
function save_cv_item(string $section, array $values, ?int $id): int
{
    $params = array_map(fn (string $field) => $values[$field], CV_ITEM_FIELDS);

    if ($id === null) {
        $position = (int) query('SELECT COALESCE(MAX(position), -1) + 1 FROM cv_items WHERE section = ?', [$section])->fetchColumn();
        query(
            'INSERT INTO cv_items (section, title, subtitle, dates, meta, url, body, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$section, ...$params, $position],
        );
        return (int) db()->lastInsertId();
    }

    query('UPDATE cv_items SET title = ?, subtitle = ?, dates = ?, meta = ?, url = ?, body = ? WHERE id = ?', [...$params, $id]);
    return $id;
}

function delete_cv_item(int $id): void
{
    query('DELETE FROM cv_items WHERE id = ?', [$id]);
}

/** Swap an entry with its neighbour above ('up') or below ('down') in its section. */
function move_cv_item(int $id, string $direction): void
{
    $item = find_cv_item($id);
    if (!$item) {
        return;
    }

    $ids = array_map(
        fn (array $row) => (int) $row['id'],
        cv_items_by_section()[$item['section']] ?? [],
    );
    $index = array_search($id, $ids, true);
    $swap = $direction === 'up' ? $index - 1 : $index + 1;
    if ($index === false || !isset($ids[$swap])) {
        return;
    }
    [$ids[$index], $ids[$swap]] = [$ids[$swap], $ids[$index]];

    db()->beginTransaction();
    foreach ($ids as $position => $itemId) {
        query('UPDATE cv_items SET position = ? WHERE id = ?', [$position, $itemId]);
    }
    db()->commit();
}
