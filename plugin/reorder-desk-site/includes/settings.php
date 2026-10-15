<?php
/**
 * Settings > Reorder Desk site: app install URL, button label, support and notification emails.
 *
 * @package reorder-desk-site
 */

defined( 'ABSPATH' ) || exit;

/** Defaults for every setting. */
function rds_defaults() {
	return array(
		'app_url'       => '', // App Store listing URL; empty = the app is not live yet, buttons go to /early-access/.
		'live_label'    => 'Install free on Shopify',
		'support_email' => '',
		'notify_email'  => '', // Where new form entries are emailed; empty = the site admin email.
	);
}

/**
 * One setting's value.
 *
 * @param string $key Setting name from rds_defaults().
 * @return string
 */
function rds_setting( $key ) {
	$all = wp_parse_args( (array) get_option( 'rds_settings', array() ), rds_defaults() );
	return isset( $all[ $key ] ) ? (string) $all[ $key ] : '';
}

/**
 * Cleans the settings form.
 *
 * @param mixed $input Posted values.
 * @return array
 */
function rds_sanitize_settings( $input ) {
	$input = is_array( $input ) ? $input : array();
	$out   = rds_defaults();

	$url = isset( $input['app_url'] ) ? esc_url_raw( trim( $input['app_url'] ), array( 'https' ) ) : '';
	if ( '' !== $url && ! wp_http_validate_url( $url ) ) {
		add_settings_error( 'rds_settings', 'rds_app_url', __( 'The app URL must be a full https:// address.', 'reorder-desk-site' ) );
		$url = '';
	}
	$out['app_url'] = $url;

	$label             = isset( $input['live_label'] ) ? sanitize_text_field( $input['live_label'] ) : '';
	$out['live_label'] = '' !== $label ? mb_substr( $label, 0, 60 ) : rds_defaults()['live_label'];

	foreach ( array( 'support_email', 'notify_email' ) as $key ) {
		$email       = isset( $input[ $key ] ) ? sanitize_email( $input[ $key ] ) : '';
		$out[ $key ] = is_email( $email ) ? $email : '';
	}
	return $out;
}

add_action(
	'admin_init',
	function () {
		register_setting(
			'rds_settings',
			'rds_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'rds_sanitize_settings',
				'default'           => rds_defaults(),
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_options_page(
			__( 'Reorder Desk site', 'reorder-desk-site' ),
			__( 'Reorder Desk site', 'reorder-desk-site' ),
			'manage_options',
			'rds-settings',
			'rds_render_settings_page'
		);
	}
);

/** Settings page. */
function rds_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = wp_parse_args( (array) get_option( 'rds_settings', array() ), rds_defaults() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Reorder Desk site', 'reorder-desk-site' ); ?></h1>
		<?php settings_errors( 'rds_settings' ); ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'rds_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rds-app-url"><?php esc_html_e( 'App Store URL', 'reorder-desk-site' ); ?></label></th>
					<td>
						<input name="rds_settings[app_url]" id="rds-app-url" type="url" class="regular-text" value="<?php echo esc_attr( $s['app_url'] ); ?>" placeholder="https://apps.shopify.com/reorder-desk">
						<p class="description"><?php esc_html_e( 'Leave empty until the app is approved. Once filled in, every button with the class "rd-cta" (header, home page, pricing, call-to-action bands) links here and shows the label below instead of "Get early access".', 'reorder-desk-site' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rds-live-label"><?php esc_html_e( 'Button label when live', 'reorder-desk-site' ); ?></label></th>
					<td><input name="rds_settings[live_label]" id="rds-live-label" type="text" class="regular-text" value="<?php echo esc_attr( $s['live_label'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rds-support-email"><?php esc_html_e( 'Support email', 'reorder-desk-site' ); ?></label></th>
					<td>
						<input name="rds_settings[support_email]" id="rds-support-email" type="email" class="regular-text" value="<?php echo esc_attr( $s['support_email'] ); ?>">
						<p class="description"><?php esc_html_e( 'Shown wherever a page uses [rd_support_email]. Empty: a link to the contact page is shown instead.', 'reorder-desk-site' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="rds-notify-email"><?php esc_html_e( 'Email new sign-ups to', 'reorder-desk-site' ); ?></label></th>
					<td>
						<input name="rds_settings[notify_email]" id="rds-notify-email" type="email" class="regular-text" value="<?php echo esc_attr( $s['notify_email'] ); ?>">
						<p class="description"><?php esc_html_e( 'Empty: the site admin email. Every entry is saved under Leads either way, even when email sending does not work (as on a local computer).', 'reorder-desk-site' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
