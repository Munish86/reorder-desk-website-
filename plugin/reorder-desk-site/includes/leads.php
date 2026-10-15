<?php
/**
 * Early-access and contact forms: [rd_waitlist], [rd_contact], [rd_support_email].
 * Entries are saved in the {prefix}rd_leads table and listed under Leads in the admin.
 * Spam protection without cookies or third parties: a hidden field, a signed time stamp and a per-IP limit.
 *
 * @package reorder-desk-site
 */

defined( 'ABSPATH' ) || exit;

/** Table name. */
function rds_table() {
	global $wpdb;
	return $wpdb->prefix . 'rd_leads';
}

/** Creates or upgrades the table. */
function rds_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();
	$table   = rds_table();
	dbDelta(
		"CREATE TABLE $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		kind varchar(20) NOT NULL,
		email varchar(190) NOT NULL,
		name varchar(190) NOT NULL DEFAULT '',
		store varchar(190) NOT NULL DEFAULT '',
		current_tool varchar(60) NOT NULL DEFAULT '',
		variants varchar(30) NOT NULL DEFAULT '',
		message text NOT NULL,
		page varchar(190) NOT NULL DEFAULT '',
		created_at datetime NOT NULL,
		PRIMARY KEY  (id),
		KEY kind_created (kind,created_at),
		KEY email (email)
		) $charset;"
	);
	update_option( 'rds_db_version', RDS_DB_VERSION );
}

/** Choices shown in the early-access form; stored by key. */
function rds_tool_choices() {
	return array(
		'stocky'      => __( 'Stocky (until it closed)', 'reorder-desk-site' ),
		'spreadsheet' => __( 'Spreadsheets', 'reorder-desk-site' ),
		'other-app'   => __( 'Another inventory app', 'reorder-desk-site' ),
		'nothing'     => __( 'Nothing yet', 'reorder-desk-site' ),
	);
}

/** Variant count choices. */
function rds_variant_choices() {
	return array(
		'lt50'     => __( 'Under 50', 'reorder-desk-site' ),
		'50-500'   => __( '50 to 500', 'reorder-desk-site' ),
		'500-5000' => __( '500 to 5,000', 'reorder-desk-site' ),
		'gt5000'   => __( 'Over 5,000', 'reorder-desk-site' ),
	);
}

/** Signed time stamp: forms posted in under 3 seconds or with a forged stamp are refused. */
function rds_stamp() {
	$t = (string) time();
	return $t . '.' . hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) );
}

/**
 * Checks a posted time stamp.
 *
 * @param string $stamp Value from the form.
 * @return bool
 */
function rds_stamp_ok( $stamp ) {
	$parts = explode( '.', (string) $stamp, 2 );
	if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
		return false;
	}
	if ( ! hash_equals( hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) ), $parts[1] ) ) {
		return false;
	}
	$age = time() - (int) $parts[0];
	return $age >= 3 && $age <= 14 * DAY_IN_SECONDS;
}

/** Message shown above a form after it was sent (read from the redirect's query string). */
function rds_form_notice( $kind ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display only.
	if ( ! isset( $_GET['rd_form'], $_GET['rd_status'] ) || sanitize_key( $_GET['rd_form'] ) !== $kind ) {
		return '';
	}
	$status = sanitize_key( $_GET['rd_status'] );
	// phpcs:enable
	$messages = array(
		'ok'      => 'waitlist' === $kind
			? __( "You're on the list. We'll email you once, on the day Reorder Desk goes live on the Shopify App Store.", 'reorder-desk-site' )
			: __( 'Thanks, your message has been sent. We reply within one working day.', 'reorder-desk-site' ),
		'email'   => __( 'Please enter a valid email address.', 'reorder-desk-site' ),
		'message' => __( 'Please write a message (up to 5,000 characters).', 'reorder-desk-site' ),
		'limit'   => __( 'Too many messages from your connection in the last hour. Please try again later.', 'reorder-desk-site' ),
		'expired' => __( 'The form had been open too long or was sent too quickly. Please send it again.', 'reorder-desk-site' ),
	);
	if ( ! isset( $messages[ $status ] ) ) {
		return '';
	}
	$class = 'ok' === $status ? 'rd-alert rd-alert--ok' : 'rd-alert rd-alert--error';
	return '<div class="' . esc_attr( $class ) . '" role="status">' . esc_html( $messages[ $status ] ) . '</div>';
}

/** Hidden fields shared by both forms. */
function rds_hidden_fields( $kind ) {
	$html  = '<input type="hidden" name="action" value="rds_submit">';
	$html .= '<input type="hidden" name="rd_kind" value="' . esc_attr( $kind ) . '">';
	$html .= '<input type="hidden" name="rd_stamp" value="' . esc_attr( rds_stamp() ) . '">';
	$html .= '<input type="hidden" name="rd_page" value="' . esc_attr( (string) get_queried_object_id() ) . '">';
	// Bots fill in every field; people never see this one.
	$html .= '<div class="rd-hp" aria-hidden="true"><label>Website <input type="text" name="rd_website" tabindex="-1" autocomplete="off"></label></div>';
	return $html;
}

/** [rd_waitlist] */
function rds_waitlist_shortcode() {
	$options = '';
	foreach ( rds_tool_choices() as $key => $label ) {
		$options .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
	}
	$variants = '';
	foreach ( rds_variant_choices() as $key => $label ) {
		$variants .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
	}
	ob_start();
	?>
	<div id="rd-form-waitlist">
		<?php echo rds_form_notice( 'waitlist' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
		<form class="rd-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php echo rds_hidden_fields( 'waitlist' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
			<label><?php esc_html_e( 'Work email', 'reorder-desk-site' ); ?>
				<input type="email" name="rd_email" required maxlength="190" autocomplete="email" placeholder="you@yourstore.com">
			</label>
			<label><?php esc_html_e( 'Your store', 'reorder-desk-site' ); ?> <span class="rd-opt"><?php esc_html_e( '(optional)', 'reorder-desk-site' ); ?></span>
				<input type="text" name="rd_store" maxlength="190" placeholder="yourstore.myshopify.com or your website">
			</label>
			<div class="rd-row">
				<label><?php esc_html_e( 'What do you use today?', 'reorder-desk-site' ); ?>
					<select name="rd_tool"><option value=""><?php esc_html_e( 'Choose one', 'reorder-desk-site' ); ?></option><?php echo $options; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></select>
				</label>
				<label><?php esc_html_e( 'Product variants', 'reorder-desk-site' ); ?>
					<select name="rd_variants"><option value=""><?php esc_html_e( 'Choose one', 'reorder-desk-site' ); ?></option><?php echo $variants; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></select>
				</label>
			</div>
			<button type="submit"><?php esc_html_e( 'Get early access', 'reorder-desk-site' ); ?></button>
			<p class="rd-fine"><?php esc_html_e( 'One email when the app is live, nothing else. Your details are never shared.', 'reorder-desk-site' ); ?> <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy policy', 'reorder-desk-site' ); ?></a></p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'rd_waitlist', 'rds_waitlist_shortcode' );

/** [rd_contact] */
function rds_contact_shortcode() {
	ob_start();
	?>
	<div id="rd-form-contact">
		<?php echo rds_form_notice( 'contact' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
		<form class="rd-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php echo rds_hidden_fields( 'contact' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
			<div class="rd-row">
				<label><?php esc_html_e( 'Name', 'reorder-desk-site' ); ?>
					<input type="text" name="rd_name" maxlength="190" autocomplete="name">
				</label>
				<label><?php esc_html_e( 'Email', 'reorder-desk-site' ); ?>
					<input type="email" name="rd_email" required maxlength="190" autocomplete="email">
				</label>
			</div>
			<label><?php esc_html_e( 'Store address', 'reorder-desk-site' ); ?> <span class="rd-opt"><?php esc_html_e( '(if it is about your store)', 'reorder-desk-site' ); ?></span>
				<input type="text" name="rd_store" maxlength="190" placeholder="yourstore.myshopify.com">
			</label>
			<label><?php esc_html_e( 'Message', 'reorder-desk-site' ); ?>
				<textarea name="rd_message" required maxlength="5000"></textarea>
			</label>
			<button type="submit"><?php esc_html_e( 'Send message', 'reorder-desk-site' ); ?></button>
			<p class="rd-fine"><?php esc_html_e( 'We use your details only to answer you.', 'reorder-desk-site' ); ?> <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy policy', 'reorder-desk-site' ); ?></a></p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'rd_contact', 'rds_contact_shortcode' );

/** [rd_support_email]: the support address as a link, or a link to the contact page when none is set. */
function rds_support_email_shortcode() {
	$email = rds_setting( 'support_email' );
	if ( $email ) {
		return '<a href="mailto:' . esc_attr( antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a>';
	}
	return '<a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'our contact form', 'reorder-desk-site' ) . '</a>';
}
add_shortcode( 'rd_support_email', 'rds_support_email_shortcode' );

/**
 * Sends the visitor back to the form with a status.
 *
 * @param string $kind   waitlist|contact.
 * @param string $status Status key for rds_form_notice().
 * @param int    $page   Page the form was on.
 */
function rds_back( $kind, $status, $page ) {
	$url = $page ? get_permalink( $page ) : '';
	if ( ! $url ) {
		$url = home_url( '/' );
	}
	$url = add_query_arg(
		array(
			'rd_form'   => $kind,
			'rd_status' => $status,
		),
		$url
	) . '#rd-form-' . $kind;
	wp_safe_redirect( $url, 303 );
	exit;
}

/**
 * Field from the posted form, cleaned and cut to a length.
 *
 * @param string $name Field name.
 * @param int    $max  Max characters.
 * @return string
 */
function rds_post_field( $name, $max = 190 ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- public form; protected by the signed stamp, honeypot and rate limit.
	$value = isset( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : '';
	return mb_substr( $value, 0, $max );
}

/** Handles both forms (admin-post.php?action=rds_submit). */
function rds_handle_submit() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- see rds_post_field().
	$kind = isset( $_POST['rd_kind'] ) && 'contact' === $_POST['rd_kind'] ? 'contact' : 'waitlist';
	$page = isset( $_POST['rd_page'] ) ? absint( $_POST['rd_page'] ) : 0;

	// A filled honeypot is a bot: pretend it worked.
	if ( ! empty( $_POST['rd_website'] ) ) {
		rds_back( $kind, 'ok', $page );
	}
	if ( ! rds_stamp_ok( isset( $_POST['rd_stamp'] ) ? sanitize_text_field( wp_unslash( $_POST['rd_stamp'] ) ) : '' ) ) {
		rds_back( $kind, 'expired', $page );
	}

	$email = sanitize_email( rds_post_field( 'rd_email' ) );
	if ( ! is_email( $email ) ) {
		rds_back( $kind, 'email', $page );
	}

	$message = '';
	if ( 'contact' === $kind ) {
		$message = isset( $_POST['rd_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['rd_message'] ) ) : '';
		if ( '' === trim( $message ) || mb_strlen( $message ) > 5000 ) {
			rds_back( $kind, 'message', $page );
		}
	}
	// phpcs:enable

	// At most 6 entries per hour from one connection. The IP itself is never stored.
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key   = 'rds_rl_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 32 );
	$count = (int) get_transient( $key );
	if ( $count >= 6 ) {
		rds_back( $kind, 'limit', $page );
	}
	set_transient( $key, $count + 1, HOUR_IN_SECONDS );

	$tool     = rds_post_field( 'rd_tool', 30 );
	$variants = rds_post_field( 'rd_variants', 30 );
	$row      = array(
		'kind'         => $kind,
		'email'        => $email,
		'name'         => rds_post_field( 'rd_name' ),
		'store'        => rds_post_field( 'rd_store' ),
		'current_tool' => isset( rds_tool_choices()[ $tool ] ) ? $tool : '',
		'variants'     => isset( rds_variant_choices()[ $variants ] ) ? $variants : '',
		'message'      => $message,
		'page'         => $page ? (string) get_the_title( $page ) : '',
		'created_at'   => current_time( 'mysql', true ),
	);

	global $wpdb;
	$table = rds_table();
	// Signing up twice for early access updates the first entry instead of adding a second.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$existing = 'waitlist' === $kind ? $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE kind = 'waitlist' AND email = %s", $email ) ) : null; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	if ( $existing ) {
		$update = array_filter( array_intersect_key( $row, array_flip( array( 'store', 'current_tool', 'variants' ) ) ) );
		if ( $update ) {
			$wpdb->update( $table, $update, array( 'id' => (int) $existing ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
	} else {
		$wpdb->insert( $table, $row ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		rds_notify( $row );
	}
	rds_back( $kind, 'ok', $page );
}
add_action( 'admin_post_nopriv_rds_submit', 'rds_handle_submit' );
add_action( 'admin_post_rds_submit', 'rds_handle_submit' );

/**
 * Emails a new entry to the owner. Failure is fine: the entry is already saved.
 *
 * @param array $row Saved row.
 */
function rds_notify( $row ) {
	$to = rds_setting( 'notify_email' );
	if ( ! $to ) {
		$to = get_option( 'admin_email' );
	}
	$subject = 'contact' === $row['kind'] ? 'New message from the Reorder Desk website' : 'New early-access sign-up: ' . $row['email'];
	$lines   = array(
		'Email: ' . $row['email'],
		'Name: ' . $row['name'],
		'Store: ' . $row['store'],
		'Uses today: ' . ( rds_tool_choices()[ $row['current_tool'] ] ?? '' ),
		'Variants: ' . ( rds_variant_choices()[ $row['variants'] ] ?? '' ),
		'Page: ' . $row['page'],
		'',
		$row['message'],
		'',
		'All entries: ' . admin_url( 'admin.php?page=rds-leads' ),
	);
	$headers = array( 'Reply-To: ' . $row['email'] );
	wp_mail( $to, $subject, implode( "\n", $lines ), $headers );
}
