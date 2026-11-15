# Blogsite

A small personal site in Rails whose main job is to be a CV. The front page is
the CV, and `/writing` is a blog. You edit both in the browser at `/admin`.
Content is stored in SQLite.

## Layout

| Path | Purpose |
| --- | --- |
| `app/models/post.rb` | Blog posts: drafts, scheduled posts, addresses (slugs) |
| `app/models/profile.rb` | The CV's name, contact details, summary and search fields |
| `app/models/cv_item.rb` | CV entries, and `SECTIONS`, which defines each CV section's fields |
| `app/controllers/admin/` | Admin pages |
| `app/views/` | HTML; `app/views/admin/` for the admin |
| `app/helpers/application_helper.rb` | Markdown, page titles, form fields |
| `app/assets/stylesheets/` | `site.css` and `print.css` |

## Setup

Requires Ruby 3.4 (via rbenv).

```bash
bundle install
bin/rails db:prepare
bin/rails admin:user   # asks for the admin email address and password
bin/rails server
```

Then open <http://localhost:3000>, and log in at <http://localhost:3000/admin>.

Run `bin/rails admin:user` again to change the password. That logs out every
device that was logged in.

## Using the admin

- **Posts:** write in Markdown. Leave a post as a **Draft** while you work on
  it; drafts are visible only to you (when logged in, they appear on the site
  marked "Draft"). **Published** posts with a future date go live on that day.
- **CV:** edit the profile (name, headline, contact details, links, summary),
  and add, edit, reorder (↑ ↓) or delete entries under Experience, Education and
  Projects. Skills is a single box of bullet points. An empty section is hidden
  on the CV.
- **Search engines** (in the CV profile): *Search description* becomes the
  page's meta description, which Google may show under your link. *Hidden note
  for machines* is in the page source for crawlers to read, but isn't shown on
  screen, read by screen readers or printed.

To get a PDF of the CV, print the front page from a browser (Ctrl+P) and choose
**Save as PDF**. The print stylesheet lays it out on A4, without the header and
footer.

## Security notes

- There's a single admin login, created with `bin/rails admin:user`.
- After 5 login attempts in 15 minutes from one IP address, logins from that
  address are blocked until the 15 minutes are up.
- Admin pages are sent with `noindex` and `no-store`. The site sends no
  JavaScript, and the Content-Security-Policy blocks scripts entirely.

## Tests

```bash
bin/rails test
```
