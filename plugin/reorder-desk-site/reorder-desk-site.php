<?php
/**
 * Plugin Name: Reorder Desk site
 * Description: Early-access and contact forms (saved in WordPress, exported as CSV), one switch that turns every "Get early access" button into an install button once the app is live, and page meta / app schema for search engines.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: Reorder Desk
 * License: GPL-2.0-or-later
 * Text Domain: reorder-desk-site
 *
 * @package reorder-desk-site
 */

defined( 'ABSPATH' ) || exit;

define( 'RDS_DB_VERSION', '1' );

require __DIR__ . '/includes/settings.php';
require __DIR__ . '/includes/leads.php';
require __DIR__ . '/includes/admin.php';
require __DIR__ . '/includes/cta.php';
require __DIR__ . '/includes/meta.php';

register_activation_hook( __FILE__, 'rds_install' );

// Also creates or upgrades the table when the plugin was copied in without being re-activated (e.g. after a migration).
add_action(
	'plugins_loaded',
	function () {
		if ( get_option( 'rds_db_version' ) !== RDS_DB_VERSION ) {
			rds_install();
		}
	}
);
