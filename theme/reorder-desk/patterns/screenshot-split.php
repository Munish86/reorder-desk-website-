<?php
/**
 * Title: Text beside a screenshot
 * Slug: reorder-desk/screenshot-split
 * Categories: reorder-desk, features
 * Description: Heading, text and a check list on one side, an app screenshot on the other. Add the class rd-split--flip to the columns to swap sides.
 *
 * @package reorder-desk
 */

$img = esc_url( get_theme_file_uri( 'assets/images/app-reorder-list.webp' ) );
?>
<!-- wp:group {"align":"full","className":"rd-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull rd-section"><!-- wp:columns {"align":"wide","className":"rd-split"} -->
<div class="wp-block-columns alignwide rd-split"><!-- wp:column {"width":"42%"} -->
<div class="wp-block-column" style="flex-basis:42%"><!-- wp:paragraph {"className":"rd-eyebrow"} -->
<p class="rd-eyebrow">Section label</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What this part of the app does</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Two or three sentences about it.</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"rd-check"} -->
<ul class="wp-block-list rd-check"><!-- wp:list-item -->
<li>First detail</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Second detail</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"full","className":"rd-shot"} -->
<figure class="wp-block-image size-full rd-shot"><img src="<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>" alt="Reorder Desk screenshot"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
