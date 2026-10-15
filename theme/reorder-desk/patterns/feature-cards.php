<?php
/**
 * Title: Feature cards (3 columns)
 * Slug: reorder-desk/feature-cards
 * Categories: reorder-desk, features
 * Description: Section heading and three cards with icons. Change an icon by editing the heading's class (rd-ic--chart, --why, --doc, --box, --layers, --upload, --bell, --archive, --pin, --calendar, --mail, --undo, --shield, --clock, --sheet).
 *
 * @package reorder-desk
 */
?>
<!-- wp:group {"align":"full","className":"rd-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull rd-section"><!-- wp:group {"className":"rd-section-head","layout":{"type":"constrained"}} -->
<div class="wp-block-group rd-section-head"><!-- wp:paragraph {"className":"rd-eyebrow"} -->
<p class="rd-eyebrow">Section label</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">A short, clear section heading</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"rd-lead"} -->
<p class="rd-lead">One or two sentences that say what this section is about.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:columns {"align":"wide","className":"rd-cards"} -->
<div class="wp-block-columns alignwide rd-cards"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"rd-card","layout":{"type":"default"}} -->
<div class="wp-block-group rd-card"><!-- wp:heading {"level":3,"className":"rd-ic rd-ic--chart"} -->
<h3 class="wp-block-heading rd-ic rd-ic--chart">First point</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>What it does for the merchant, in one or two sentences.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"rd-card","layout":{"type":"default"}} -->
<div class="wp-block-group rd-card"><!-- wp:heading {"level":3,"className":"rd-ic rd-ic--doc"} -->
<h3 class="wp-block-heading rd-ic rd-ic--doc">Second point</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>What it does for the merchant, in one or two sentences.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"rd-card","layout":{"type":"default"}} -->
<div class="wp-block-group rd-card"><!-- wp:heading {"level":3,"className":"rd-ic rd-ic--box"} -->
<h3 class="wp-block-heading rd-ic rd-ic--box">Third point</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>What it does for the merchant, in one or two sentences.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
