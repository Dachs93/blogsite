<?php

declare(strict_types=1);

/**
 * A blog post from content/posts/YYYY-MM-DD-slug.md.
 */
final class Post
{
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly DateTimeImmutable $date,
        public readonly string $summary,
        public readonly string $body,
        public readonly bool $draft,
    ) {
    }

    public function url(): string
    {
        return '/writing/' . $this->slug;
    }

    public function html(): string
    {
        return md($this->body);
    }

    /** Live on the site: not a draft, and its date has arrived (so you can schedule posts). */
    public function isPublished(): bool
    {
        return !$this->draft && $this->date <= new DateTimeImmutable('today');
    }
}

const POST_FILENAME = '/^(\d{4}-\d{2}-\d{2})-([a-z0-9-]+)\.md$/';

/**
 * Published posts, newest first. Locally (DEV) drafts and future posts are included too.
 *
 * @return list<Post>
 */
function all_posts(): array
{
    $posts = [];
    foreach (glob(ROOT . '/content/posts/*.md') ?: [] as $file) {
        $post = load_post($file);
        if ($post && (DEV || $post->isPublished())) {
            $posts[] = $post;
        }
    }

    usort($posts, fn (Post $a, Post $b) => [$b->date, $b->slug] <=> [$a->date, $a->slug]);

    return $posts;
}

function find_post(string $slug): ?Post
{
    foreach (all_posts() as $post) {
        if ($post->slug === $slug) {
            return $post;
        }
    }

    return null;
}

function load_post(string $file): ?Post
{
    if (!preg_match(POST_FILENAME, basename($file), $m)) {
        return null;
    }

    [$meta, $body] = parse_front_matter((string) file_get_contents($file));

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $meta['date'] ?? $m[1]);
    if ($date === false) {
        return null;
    }

    return new Post(
        slug: $m[2],
        title: $meta['title'] ?? ucfirst(str_replace('-', ' ', $m[2])),
        date: $date,
        summary: $meta['summary'] ?? '',
        body: $body,
        draft: in_array(strtolower($meta['draft'] ?? 'false'), ['true', 'yes', '1'], true),
    );
}

/**
 * Split "---\nkey: value\n---\nbody" into ([key => value], body). Values are plain strings.
 *
 * @return array{0: array<string, string>, 1: string}
 */
function parse_front_matter(string $raw): array
{
    $raw = str_replace("\r\n", "\n", $raw);
    if (!preg_match('/\A---\n(.*?)\n---\n?(.*)\z/s', $raw, $m)) {
        return [[], $raw];
    }

    $meta = [];
    foreach (explode("\n", $m[1]) as $line) {
        if (preg_match('/^([a-z_]+):\s*(.*)$/i', $line, $kv)) {
            $meta[strtolower($kv[1])] = trim($kv[2], " \t\"'");
        }
    }

    return [$meta, $m[2]];
}
