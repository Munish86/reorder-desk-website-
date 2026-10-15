<?php
/**
 * Leads page in the admin: early-access sign-ups and contact messages, CSV download, delete.
 *
 * @package reorder-desk-site
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			__( 'Leads', 'reorder-desk-site' ),
			__( 'Leads', 'reorder-desk-site' ),
			'manage_options',
			'rds-leads',
			'rds_render_leads_page',
			'dashicons-groups',
			26
		);
	}
);

/** Counts per kind. */
function rds_counts() {
	global $wpdb;
	$table = rds_table();
	$rows  = $wpdb->get_results( "SELECT kind, COUNT(*) AS n FROM $table GROUP BY kind", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$out   = array(
		'waitlist' => 0,
		'contact'  => 0,
	);
	foreach ( (array) $rows as $r ) {
		$out[ $r['kind'] ] = (int) $r['n'];
	}
	return $out;
}

/** The Leads page. */
function rds_render_leads_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$table = rds_table();
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only view parameters.
	$kind  = isset( $_GET['kind'] ) && 'contact' === $_GET['kind'] ? 'contact' : 'waitlist';
	$paged = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$done  = isset( $_GET['rds_deleted'] );
	// phpcs:enable
	$per    = 50;
	$counts = rds_counts();
	$total  = $counts[ $kind ];
	$rows   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE kind = %s ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", $kind, $per, ( $paged - 1 ) * $per ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$base   = admin_url( 'admin.php?page=rds-leads' );
	$export = wp_nonce_url( admin_url( 'admin-post.php?action=rds_export&kind=' . $kind ), 'rds_export' );
	$tools  = rds_tool_choices();
	$sizes  = rds_variant_choices();
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Leads', 'reorder-desk-site' ); ?></h1>
		<a href="<?php echo esc_url( $export ); ?>" class="page-title-action"><?php esc_html_e( 'Download CSV', 'reorder-desk-site' ); ?></a>
		<hr class="wp-header-end">
		<?php if ( $done ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Entry deleted.', 'reorder-desk-site' ); ?></p></div>
		<?php endif; ?>
		<ul class="subsubsub">
			<li><a href="<?php echo esc_url( $base ); ?>" <?php echo 'waitlist' === $kind ? 'class="current" aria-current="page"' : ''; ?>><?php esc_html_e( 'Early access', 'reorder-desk-site' ); ?> <span class="count">(<?php echo (int) $counts['waitlist']; ?>)</span></a> |</li>
			<li><a href="<?php echo esc_url( add_query_arg( 'kind', 'contact', $base ) ); ?>" <?php echo 'contact' === $kind ? 'class="current" aria-current="page"' : ''; ?>><?php esc_html_e( 'Contact messages', 'reorder-desk-site' ); ?> <span class="count">(<?php echo (int) $counts['contact']; ?>)</span></a></li>
		</ul>
		<table class="widefat striped" style="margin-top:12px">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'reorder-desk-site' ); ?></th>
					<th><?php esc_html_e( 'Email', 'reorder-desk-site' ); ?></th>
					<?php if ( 'contact' === $kind ) : ?>
						<th><?php esc_html_e( 'Name', 'reorder-desk-site' ); ?></th>
					<?php endif; ?>
					<th><?php esc_html_e( 'Store', 'reorder-desk-site' ); ?></th>
					<?php if ( 'waitlist' === $kind ) : ?>
						<th><?php esc_html_e( 'Uses today', 'reorder-desk-site' ); ?></th>
						<th><?php esc_html_e( 'Variants', 'reorder-desk-site' ); ?></th>
					<?php else : ?>
						<th style="width:40%"><?php esc_html_e( 'Message', 'reorder-desk-site' ); ?></th>
					<?php endif; ?>
					<th><?php esc_html_e( 'Page', 'reorder-desk-site' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( ! $rows ) : ?>
				<tr><td colspan="7"><?php esc_html_e( 'Nothing yet.', 'reorder-desk-site' ); ?></td></tr>
			<?php endif; ?>
			<?php foreach ( $rows as $r ) : ?>
				<?php $del = wp_nonce_url( admin_url( 'admin-post.php?action=rds_delete&id=' . (int) $r['id'] . '&kind=' . $kind ), 'rds_delete_' . (int) $r['id'] ); ?>
				<tr>
					<td><?php echo esc_html( get_date_from_gmt( $r['created_at'], 'j M Y, H:i' ) ); ?></td>
					<td><a href="mailto:<?php echo esc_attr( $r['email'] ); ?>"><?php echo esc_html( $r['email'] ); ?></a></td>
					<?php if ( 'contact' === $kind ) : ?>
						<td><?php echo esc_html( $r['name'] ); ?></td>
					<?php endif; ?>
					<td><?php echo esc_html( $r['store'] ); ?></td>
					<?php if ( 'waitlist' === $kind ) : ?>
						<td><?php echo esc_html( $tools[ $r['current_tool'] ] ?? '' ); ?></td>
						<td><?php echo esc_html( $sizes[ $r['variants'] ] ?? '' ); ?></td>
					<?php else : ?>
						<td><?php echo nl2br( esc_html( $r['message'] ) ); ?></td>
					<?php endif; ?>
					<td><?php echo esc_html( $r['page'] ); ?></td>
					<td><a href="<?php echo esc_url( $del ); ?>" onclick="return confirm('Delete this entry?')" style="color:#b32d2e"><?php esc_html_e( 'Delete', 'reorder-desk-site' ); ?></a></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		$pages = (int) ceil( $total / $per );
		if ( $pages > 1 ) {
			echo '<div class="tablenav"><div class="tablenav-pages">';
			echo wp_kses_post(
				paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%', add_query_arg( 'kind', $kind, $base ) ),
						'format'  => '',
						'current' => $paged,
						'total'   => $pages,
					)
				)
			);
			echo '</div></div>';
		}
		?>
	</div>
	<?php
}

/**
 * A CSV cell that spreadsheet programs will not run as a formula.
 *
 * @param string $value Cell value.
 * @return string
 */
function rds_csv_cell( $value ) {
	$value = (string) $value;
	if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
		$value = "'" . $value;
	}
	return $value;
}

add_action(
	'admin_post_rds_export',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'reorder-desk-site' ), 403 );
		}
		check_admin_referer( 'rds_export' );
		global $wpdb;
		$table = rds_table();
		$kind  = isset( $_GET['kind'] ) && 'contact' === $_GET['kind'] ? 'contact' : 'waitlist';
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE kind = %s ORDER BY created_at ASC, id ASC", $kind ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$tools = rds_tool_choices();
		$sizes = rds_variant_choices();

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="reorder-desk-' . $kind . '-' . gmdate( 'Y-m-d' ) . '.csv"' );
		header( 'X-Content-Type-Options: nosniff' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // BOM, so Excel reads UTF-8.
		fputcsv( $out, array( 'Date (UTC)', 'Email', 'Name', 'Store', 'Uses today', 'Variants', 'Message', 'Page' ) );
		foreach ( $rows as $r ) {
			fputcsv(
				$out,
				array_map(
					'rds_csv_cell',
					array( $r['created_at'], $r['email'], $r['name'], $r['store'], $tools[ $r['current_tool'] ] ?? '', $sizes[ $r['variants'] ] ?? '', $r['message'], $r['page'] )
				)
			);
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}
);

add_action(
	'admin_post_rds_delete',
	function () {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'reorder-desk-site' ), 403 );
		}
		check_admin_referer( 'rds_delete_' . $id );
		global $wpdb;
		$wpdb->delete( rds_table(), array( 'id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$kind = isset( $_GET['kind'] ) && 'contact' === $_GET['kind'] ? 'contact' : 'waitlist';
		wp_safe_redirect( add_query_arg( array( 'kind' => $kind, 'rds_deleted' => 1 ), admin_url( 'admin.php?page=rds-leads' ) ) );
		exit;
	}
);
