<?php
/**
 * Puts the pages and blog posts from content/ into WordPress and sets the home page, blog page and privacy page.
 *
 * Usage (from reorder-desk-website/):
 *   tools/wp.sh eval-file tools/setup-content.php --user=reorderadmin            # only adds what is missing
 *   tools/wp.sh eval-file tools/setup-content.php force --user=reorderadmin      # also overwrites existing pages/posts
 *   tools/wp.sh eval-file tools/setup-content.php force only=pricing --user=reorderadmin
 *
 * Without "force", pages already in WordPress are left alone, so edits made in the WordPress editor are never lost.
 *
 * @package reorder-desk
 */

// phpcs:disable WordPress.WP.AlternativeFunctions -- CLI script reading local files.

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$rd_root  = dirname( __DIR__ ) . '/content/';
$rd_site  = require $rd_root . 'site.php';
$rd_force = in_array( 'force', $args, true );
$rd_only  = '';
foreach ( $args as $rd_arg ) {
	if ( 0 === strpos( $rd_arg, 'only=' ) ) {
		$rd_only = substr( $rd_arg, 5 );
	}
}
if ( ! current_user_can( 'unfiltered_html' ) ) {
	WP_CLI::error( 'Run with --user=<admin user> so page markup is saved as written.' );
}

/**
 * Reads a content file.
 *
 * @param string $root Content folder.
 * @param string $file Relative path; empty = empty content.
 * @return string
 */
function rd_read( $root, $file ) {
	if ( '' === $file ) {
		return '';
	}
	$text = file_get_contents( $root . $file );
	if ( false === $text ) {
		WP_CLI::error( "Missing content file: $file" );
	}
	return trim( $text ) . "\n";
}

/**
 * Creates or updates one post/page. Returns its id, or the existing id when it is kept.
 *
 * @param string $type  page|post.
 * @param string $slug  URL slug.
 * @param array  $data  Post fields.
 * @param bool   $force Overwrite when it exists.
 * @return int
 */
function rd_upsert( $type, $slug, $data, $force ) {
	$existing = get_page_by_path( $slug, OBJECT, $type );
	if ( $existing && ! $force ) {
		WP_CLI::log( "kept    $type $slug (already there; use force to overwrite)" );
		return (int) $existing->ID;
	}
	$data = array_merge(
		array(
			'post_type'      => $type,
			'post_status'    => 'publish',
			'post_name'      => $slug,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		),
		$data
	);
	if ( $existing ) {
		$data['ID'] = $existing->ID;
		$id         = wp_update_post( wp_slash( $data ), true );
	} else {
		$id = wp_insert_post( wp_slash( $data ), true );
	}
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( "$slug: " . $id->get_error_message() );
	}
	WP_CLI::log( ( $existing ? 'updated ' : 'created ' ) . "$type $slug" );
	return (int) $id;
}

/**
 * Adds a cover image from content/covers/ to the media library once and returns its id.
 *
 * @param string $root  Content folder.
 * @param string $file  File name in covers/.
 * @param string $alt   Alt text.
 * @return int
 */
function rd_cover( $root, $file, $alt ) {
	$found = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_rd_cover', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $file, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $found ) {
		return (int) $found[0];
	}
	$path = $root . 'covers/' . $file;
	if ( ! is_file( $path ) ) {
		WP_CLI::warning( "No cover image $file" );
		return 0;
	}
	$tmp = wp_tempnam( $file );
	copy( $path, $tmp );
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), 0, $alt );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( "$file: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_rd_cover', $file );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

// ---- Pages ----
$rd_ids = array();
foreach ( $rd_site['pages'] as $rd_slug => $rd_page ) {
	if ( $rd_only && $rd_only !== $rd_slug ) {
		$found = get_page_by_path( $rd_slug );
		if ( $found ) {
			$rd_ids[ $rd_slug ] = (int) $found->ID;
		}
		continue;
	}
	$rd_id              = rd_upsert(
		'page',
		$rd_slug,
		array(
			'post_title'   => $rd_page['title'],
			'post_content' => rd_read( $rd_root, $rd_page['file'] ),
			'post_excerpt' => $rd_page['desc'],
		),
		$rd_force
	);
	$rd_ids[ $rd_slug ] = $rd_id;
	update_post_meta( $rd_id, '_wp_page_template', $rd_page['template'] ?? '' );
	update_post_meta( $rd_id, '_yoast_wpseo_metadesc', $rd_page['desc'] );
	update_post_meta( $rd_id, '_yoast_wpseo_title', 'home' === $rd_slug ? $rd_page['seo'] : $rd_page['seo'] . ' | Reorder Desk' );
}

if ( isset( $rd_ids['home'], $rd_ids['blog'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $rd_ids['home'] );
	update_option( 'page_for_posts', $rd_ids['blog'] );
}
if ( isset( $rd_ids['privacy-policy'] ) ) {
	update_option( 'wp_page_for_privacy_policy', $rd_ids['privacy-policy'] );
}

// ---- Posts ----
foreach ( $rd_site['posts'] as $rd_slug => $rd_post ) {
	if ( $rd_only && $rd_only !== $rd_slug ) {
		continue;
	}
	$rd_cat = (int) wp_create_category( $rd_post['category'] );
	$rd_id  = rd_upsert(
		'post',
		$rd_slug,
		array(
			'post_title'    => $rd_post['title'],
			'post_content'  => rd_read( $rd_root, $rd_post['file'] ),
			'post_excerpt'  => $rd_post['desc'],
			'post_date'     => $rd_post['date'],
			'post_category' => array( $rd_cat ),
		),
		$rd_force
	);
	update_post_meta( $rd_id, '_yoast_wpseo_metadesc', $rd_post['desc'] );
	if ( ! empty( $rd_post['cover'] ) ) {
		$rd_img = rd_cover( $rd_root, $rd_post['cover'], $rd_post['title'] );
		if ( $rd_img ) {
			set_post_thumbnail( $rd_id, $rd_img );
		}
	}
}

// The default category is "Guides" instead of "Uncategorized".
$rd_guides = get_term_by( 'name', 'Guides', 'category' );
if ( $rd_guides ) {
	update_option( 'default_category', $rd_guides->term_id );
	$rd_uncat = get_term_by( 'slug', 'uncategorized', 'category' );
	if ( $rd_uncat && 0 === (int) $rd_uncat->count ) {
		wp_delete_term( $rd_uncat->term_id, 'category' );
	}
}

flush_rewrite_rules( false );
WP_CLI::success( 'Content is in place.' );
