# Blogsite

A small personal site in plain PHP whose main job is to be a CV. The front page
is the CV, and `/writing` is a blog. You edit both in the browser at `/admin`.
There's no framework and no build step. Content is stored in MariaDB (or MySQL).

## Layout

| Path | Purpose |
| --- | --- |
| `public/` | Web root: `index.php` (every request goes through it) and CSS |
| `src/app.php` | Start-up, routing and helpers |
| `src/db.php` | Database connection and table definitions |
| `src/auth.php` | Admin login, login rate limit and form protection |
| `src/posts.php`, `src/cv.php` | Reading and saving posts and the CV |
| `src/admin.php` | Admin pages |
| `templates/` | HTML for the public pages; `templates/admin/` for the admin |
| `lib/Parsedown.php` | Markdown parser ([Parsedown](https://parsedown.org) 1.7.4, MIT) |
| `bin/hash-password.php` | Makes a password hash for `config.php` |
| `config.example.php` | Settings template. Copy it to `config.php`, which git ignores |

Only `public/` should be served. If the web server's document root is the repo
folder instead, the top-level `.htaccess` sends every request into `public/`, so
`config.php`, `src/` and the rest are never served directly.

## Setup

Requires PHP 8.1 or newer with `pdo_mysql`, and an empty MariaDB/MySQL database.

1. `cp config.example.php config.php`
2. Fill in the database details.
3. Set the admin password: run `php bin/hash-password.php` and paste the output
   into `password_hash`.
4. Set `secret`: run `php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'` and paste
   the output.

The tables are created automatically on the first page load.

To change the password later, generate a new hash and replace it in
`config.php`. That also logs out every device that was logged in.

## Using the admin

Log in at `/admin`.

- **Posts:** write in Markdown. Leave a post as a **Draft** while you work on
  it; drafts are visible only to you (when logged in, they appear on the site
  marked "Draft"). **Published** posts with a future date go live on that day.
- **CV:** edit the profile (name, headline, contact details, links, summary),
  and add, edit, reorder (↑ ↓) or delete entries under Experience, Education,
  Skills and Projects. An empty section is hidden on the CV.

To get a PDF of the CV, print the front page from a browser (Ctrl+P) and choose
**Save as PDF**. The print stylesheet lays it out on A4, without the header and
footer.

## Security notes

- There's a single admin account, whose password hash is stored in `config.php`.
- Login is a signed, HttpOnly cookie that lasts 14 days. It's sent over HTTPS
  only when the site is served over HTTPS.
- After 5 failed logins from one IP address, logins from that address are
  blocked for 15 minutes.
- Every admin form carries a token, so other sites can't submit changes on your
  behalf.
- Admin pages are sent with `noindex` and `no-store`. The site sends no
  JavaScript, and the Content-Security-Policy blocks scripts entirely.

## Running locally

```bash
php -S localhost:8080 -t public public/index.php
```

Then open <http://localhost:8080>. Locally, PHP errors are shown on the page; on
the live site they're logged and visitors see a plain error page.
