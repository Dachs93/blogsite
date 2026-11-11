<?php
/**
 * Title: CV page
 * Slug: blogsite/cv
 * Categories: cv
 * Block Types: core/post-content
 * Post Types: page
 * Description: A complete CV to fill in: name and contact details, summary, experience, education, skills and projects.
 */
?>
<!-- wp:group {"tagName":"header","className":"cv-header","layout":{"type":"default"}} -->
<header class="wp-block-group cv-header"><!-- wp:heading {"level":1,"className":"cv-name"} -->
<h1 class="wp-block-heading cv-name">Your Name</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"cv-headline"} -->
<p class="cv-headline">What you do, in a few words</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"cv-contact"} -->
<p class="cv-contact">City, Country · <a href="mailto:you@example.com">you@example.com</a> · <a href="https://github.com/">GitHub</a> · <a href="https://www.linkedin.com/">LinkedIn</a></p>
<!-- /wp:paragraph --></header>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"cv-section","layout":{"type":"default"}} -->
<section class="wp-block-group cv-section"><!-- wp:heading {"className":"cv-section-title"} -->
<h2 class="wp-block-heading cv-section-title">Summary</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Two or three sentences: what you're good at, what you've done, and what you're looking for next.</p>
<!-- /wp:paragraph --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"cv-section","layout":{"type":"default"}} -->
<section class="wp-block-group cv-section"><!-- wp:heading {"className":"cv-section-title"} -->
<h2 class="wp-block-heading cv-section-title">Experience</h2>
<!-- /wp:heading -->

<!-- wp:pattern {"slug":"blogsite/cv-experience"} /-->

<!-- wp:pattern {"slug":"blogsite/cv-experience"} /--></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"cv-section","layout":{"type":"default"}} -->
<section class="wp-block-group cv-section"><!-- wp:heading {"className":"cv-section-title"} -->
<h2 class="wp-block-heading cv-section-title">Education</h2>
<!-- /wp:heading -->

<!-- wp:pattern {"slug":"blogsite/cv-education"} /--></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"cv-section cv-skills","layout":{"type":"default"}} -->
<section class="wp-block-group cv-section cv-skills"><!-- wp:heading {"className":"cv-section-title"} -->
<h2 class="wp-block-heading cv-section-title">Skills</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>Languages:</strong> list them here</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Frameworks and tools:</strong> list them here</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Other:</strong> spoken languages, certifications, and so on</p>
<!-- /wp:paragraph --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"cv-section","layout":{"type":"default"}} -->
<section class="wp-block-group cv-section"><!-- wp:heading {"className":"cv-section-title"} -->
<h2 class="wp-block-heading cv-section-title">Projects</h2>
<!-- /wp:heading -->

<!-- wp:pattern {"slug":"blogsite/cv-project"} /--></section>
<!-- /wp:group -->
