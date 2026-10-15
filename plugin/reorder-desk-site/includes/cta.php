<?php
/**
 * Launch switch: while no App Store URL is set, buttons with the class "rd-cta" keep their own link and label
 * (Get early access -> /early-access/). Once the URL is set in Settings > Reorder Desk site, every such button
 * links to the App Store listing with the "live" label. Nothing in the pages has to be edited on launch day.
 *
 * @package reorder-desk-site
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'render_block_core/button',
	function ( $html, $block ) {
		$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
		if ( ! preg_match( '/(^|\s)rd-cta(\s|$)/', $class ) ) {
			return $html;
		}
		$url = rds_setting( 'app_url' );
		if ( '' === $url ) {
			return $html;
		}
		$p = new WP_HTML_Tag_Processor( $html );
		if ( $p->next_tag( 'a' ) ) {
			$p->set_attribute( 'href', esc_url( $url ) );
			$p->set_attribute( 'rel', 'noopener' );
		}
		$label = esc_html( rds_setting( 'live_label' ) );
		return preg_replace_callback(
			'#(<a\b[^>]*>)(.*?)(</a>)#s',
			function ( $m ) use ( $label ) {
				return $m[1] . $label . $m[3];
			},
			$p->get_updated_html(),
			1
		);
	},
	10,
	2
);
