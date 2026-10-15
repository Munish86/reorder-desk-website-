<?php
/**
 * Reorder Desk theme setup. Design tokens are in theme.json; component styles in assets/css/site.css.
 *
 * @package reorder-desk
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/site.css' );
		add_theme_support( 'responsive-embeds' );
		// Pages get an excerpt box: the plugin uses it as the page's meta description when no SEO plugin is active.
		add_post_type_support( 'page', 'excerpt' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$file = get_theme_file_path( 'assets/css/site.css' );
		wp_enqueue_style( 'reorder-desk', get_theme_file_uri( 'assets/css/site.css' ), array(), (string) filemtime( $file ) );
	}
);

// The one font file is needed on every page: start loading it before the CSS asks for it.
add_action(
	'wp_head',
	function () {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( 'assets/fonts/inter-latin-wght-normal.woff2' ) )
		);
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( get_theme_file_uri( 'assets/images/favicon.svg' ) ) );
		}
	},
	1
);

add_action(
	'init',
	function () {
		register_block_pattern_category( 'reorder-desk', array( 'label' => __( 'Reorder Desk', 'reorder-desk' ) ) );

		// Emoji scripts are not needed on a marketing site and cost a request on every page.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}
);

// Keep the WordPress version out of the page source.
remove_action( 'wp_head', 'wp_generator' );
