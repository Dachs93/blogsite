<?php
/**
 * Title: Post list
 * Slug: blogsite/post-list
 * Inserter: no
 * Description: Posts for the current blog, archive or search page, newest first. Shared by those templates.
 */
?>
<!-- wp:query {"queryId":1,"query":{"inherit":true},"className":"post-list"} -->
<div class="wp-block-query post-list"><!-- wp:post-template -->
<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:post-title {"level":2,"isLink":true,"fontSize":"large"} /-->

<!-- wp:post-date /-->

<!-- wp:post-excerpt {"moreText":""} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"space-between"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>Nothing here yet.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->
