# Blogsite

A small personal site in plain PHP whose main job is to be a CV. The front page
is the CV, and `/writing` is a blog. There's no database, framework or build
step: the content is files in this repo.

## Layout

| Path | Purpose |
| --- | --- |
| `content/cv.php` | **Your CV**: name, contact links, summary, experience, education, skills, projects |
| `content/posts/` | **Blog posts**, one Markdown file each |
| `public/` | The web root: `index.php` (front controller) and CSS |
| `src/` | Routing, rendering and post loading |
| `templates/` | HTML for the layout, CV, post list, post and 404 pages |
| `lib/Parsedown.php` | Markdown parser ([Parsedown](https://parsedown.org) 1.7.4, MIT) |

Only `public/` should be served. If the web server's document root is the repo
folder instead, the top-level `.htaccess` sends every request into `public/`, so
`content/`, `src/` and the rest are never served directly.

## Editing the CV

Edit `content/cv.php`. Each section is a PHP array. Add, remove or reorder
entries, and an empty list hides its section. The summary is Markdown; bullet
points, notes and project descriptions accept inline Markdown (`**bold**`,
`[links](https://example.com)`).

To get a PDF, open the CV in a browser, print it (Ctrl+P) and choose **Save as
PDF**. The print stylesheet lays it out on A4, without the header and footer.

## Writing a post

Add `content/posts/YYYY-MM-DD-your-slug.md`:

```markdown
---
title: The post's title
summary: One sentence, shown in the post list.
draft: true
---

The body, in Markdown.
```

The post's address is `/writing/your-slug`. Drafts and posts with a future date
appear only when running locally (marked "Draft"). Remove `draft: true` to
publish.

## Running locally

Requires PHP 8.1 or newer.

```bash
php -S localhost:8080 -t public public/index.php
```

Then open <http://localhost:8080>. Locally, PHP errors are shown on the page; on
the live site they are hidden.
