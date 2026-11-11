---
title: How posts work
summary: A quick reference for writing posts on this site.
draft: true
---

This post is a draft, so it only shows up when the site runs locally. Delete it
once you've written your first real post.

## Writing a post

Create a file in `content/posts/` named `YYYY-MM-DD-your-slug.md`. The date in the
name is the publish date, and the slug becomes the address: `/writing/your-slug`.

Start the file with front matter:

```
---
title: The post's title
summary: One sentence for the post list and search results.
draft: true
---
```

Remove `draft: true` (or set it to `false`) when it's ready. A post dated in the
future stays hidden until that day.

## Formatting

The body is [Markdown](https://commonmark.org/help/): **bold**, *italic*,
`inline code`, lists, links, images, quotes and fenced code blocks.

> A quote looks like this.
