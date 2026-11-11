# Blogsite

A WordPress block theme for a personal site whose main job is to be a CV. The
front page is the CV, and the "Writing" page lists blog posts.

This repository **is the theme**: its root is deployed as
`wp-content/themes/blogsite`. WordPress itself, `wp-config.php`, the database
and uploads are not in git.

## What's in it

| Path | Purpose |
| --- | --- |
| `style.css` | Theme header, site-wide styles, dark mode |
| `theme.json` | Colours, fonts, sizes and block styles used by the editor and front end |
| `functions.php` | Loads the stylesheets and registers the "CV" pattern category |
| `assets/css/cv.css` | CV layout (also loaded in the editor) |
| `assets/css/print.css` | Print / Save as PDF: A4, black on white, no header or footer |
| `templates/` | Front page (CV), pages, single posts, blog list, archives, search, 404 |
| `parts/` | Header and footer |
| `patterns/` | **CV page** (the full CV) plus job, education and project entries to add more |

## Editing the CV

The CV is an ordinary WordPress page, set as the static front page, so you edit
it in **Pages → CV** in the WordPress admin. To add another job, education entry
or project, open the inserter (**+**), go to **Patterns → CV** and insert the
matching pattern.

To get a PDF, open the front page and print it (Ctrl+P), then choose **Save as
PDF**. The print stylesheet handles the layout.

## Local development

Local WordPress lives in `~/workspace/blogsite-wp` (outside git). Its
`wp-content/themes/blogsite` is a symlink to this repository, so edits here show
up straight away.

```bash
wp server --host=localhost --port=8080 --path=$HOME/workspace/blogsite-wp
```

Then open <http://localhost:8080> (admin at `/wp-admin`).

Requirements: PHP 8.3, MariaDB and [WP-CLI](https://wp-cli.org/).

## Site Editor gotcha

If you change a template or template part in **Appearance → Editor**,
WordPress saves that copy in the database, and it takes priority over the file
in this repo. Later changes to that file then won't show up. To go back to the
file version, open the template in the Site Editor and choose **Reset**. Make
template changes here in git instead.
