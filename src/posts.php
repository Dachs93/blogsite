<?php

declare(strict_types=1);

final class Post
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $summary,
        public readonly string $body,
        public readonly DateTimeImmutable $date,
        public readonly bool $draft,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            $row['slug'],
            $row['title'],
            $row['summary'],
            $row['body'],
            new DateTimeImmutable($row['published_on']),
            (bool) $row['draft'],
        );
    }

    public function url(): string
    {
        return '/writing/' . $this->slug;
    }

    public function html(): string
    {
        return md($this->body);
    }

    /** Visible to everyone: not a draft, and its date has arrived (so posts can be scheduled). */
    public function isPublished(): bool
    {
        return $this->status() === 'Published';
    }

    /** 'Draft', 'Scheduled' or 'Published'. */
    public function status(): string
    {
        return match (true) {
            $this->draft => 'Draft',
            $this->date > new DateTimeImmutable('today') => 'Scheduled',
            default => 'Published',
        };
    }
}

/**
 * Posts newest first. Without $include_hidden, only published ones.
 *
 * @return list<Post>
 */
function all_posts(bool $include_hidden = false): array
{
    $sql = 'SELECT * FROM posts';
    $params = [];
    if (!$include_hidden) {
        $sql .= ' WHERE draft = 0 AND published_on <= ?';
        $params[] = date('Y-m-d');
    }
    $sql .= ' ORDER BY published_on DESC, id DESC';

    return array_map(Post::fromRow(...), query($sql, $params)->fetchAll());
}

function find_post_by_slug(string $slug, bool $include_hidden = false): ?Post
{
    $row = query('SELECT * FROM posts WHERE slug = ?', [$slug])->fetch();
    $post = $row ? Post::fromRow($row) : null;

    return $post && ($include_hidden || $post->isPublished()) ? $post : null;
}

function find_post(int $id): ?Post
{
    $row = query('SELECT * FROM posts WHERE id = ?', [$id])->fetch();
    return $row ? Post::fromRow($row) : null;
}

/** @return array<string, string> Form values for a new post, or an existing one. */
function post_form_values(?Post $post): array
{
    return [
        'title' => $post?->title ?? '',
        'slug' => $post?->slug ?? '',
        'summary' => $post?->summary ?? '',
        'body' => $post?->body ?? '',
        'published_on' => ($post?->date ?? new DateTimeImmutable('today'))->format('Y-m-d'),
        'status' => $post && !$post->draft ? 'published' : 'draft',
    ];
}

/**
 * Clean and check the submitted post form.
 *
 * @return array{0: array<string, string>, 1: array<string, string>} [values, errors by field]
 */
function post_input(): array
{
    $values = [
        'title' => trim(input('title')),
        'slug' => trim(input('slug')),
        'summary' => trim(input('summary')),
        'body' => str_replace("\r\n", "\n", input('body')),
        'published_on' => trim(input('published_on')) ?: date('Y-m-d'),
        'status' => input('status') === 'published' ? 'published' : 'draft',
    ];
    $values['slug'] = slugify($values['slug'] !== '' ? $values['slug'] : $values['title']);

    $errors = [];
    if ($values['title'] === '') {
        $errors['title'] = 'Give the post a title.';
    } elseif (mb_strlen($values['title']) > 255) {
        $errors['title'] = 'Keep the title under 255 characters.';
    }
    if ($values['slug'] === '') {
        $errors['slug'] = 'Use at least one letter or number.';
    }
    if (mb_strlen($values['summary']) > 500) {
        $errors['summary'] = 'Keep the summary under 500 characters.';
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $values['published_on']);
    if (!$date || $date->format('Y-m-d') !== $values['published_on']) {
        $errors['published_on'] = 'Use a date like 2026-11-11.';
    }

    return [$values, $errors];
}

/**
 * Insert or update a post.
 *
 * @param array<string, string> $values from post_input()
 * @return int|null The post's id, or null if another post already uses the slug.
 */
function save_post(array $values, ?int $id): ?int
{
    $params = [
        $values['slug'],
        $values['title'],
        $values['summary'],
        $values['body'],
        $values['published_on'],
        $values['status'] === 'draft' ? 1 : 0,
    ];

    try {
        if ($id === null) {
            query('INSERT INTO posts (slug, title, summary, body, published_on, draft) VALUES (?, ?, ?, ?, ?, ?)', $params);
            return (int) db()->lastInsertId();
        }
        query('UPDATE posts SET slug = ?, title = ?, summary = ?, body = ?, published_on = ?, draft = ? WHERE id = ?', [...$params, $id]);
        return $id;
    } catch (PDOException $e) {
        if (is_duplicate_key($e)) {
            return null;
        }
        throw $e;
    }
}

function delete_post(int $id): void
{
    query('DELETE FROM posts WHERE id = ?', [$id]);
}

/** "Fáilte, World!" → "failte-world" */
function slugify(string $text): string
{
    if (class_exists(Normalizer::class)) {
        $text = preg_replace('/\p{Mn}/u', '', (string) Normalizer::normalize($text, Normalizer::FORM_D)) ?? $text;
    }
    $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-');

    return rtrim(substr($slug, 0, 190), '-');
}
