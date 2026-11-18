<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Addons' ) ) :

	final class WPSC_Addons {

		/**
		 * Registered add-ons
		 *
		 * @var array
		 */
		private static $addons = array();

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// Registry is not needed while database upgrade is in progress.
			if ( defined( 'WPSC_DB_UPGRADING' ) ) {
				return;
			}

			// license activate and deactivate.
			add_action( 'wp_ajax_wpsc_addon_license_activate', array( __CLASS__, 'activate' ) );
			add_action( 'wp_ajax_wpsc_addon_license_deactivate', array( __CLASS__, 'deactivate' ) );

			// daily license check (one batched run for all registered add-ons).
			add_action( 'wpsc_license_checker', array( __CLASS__, 'license_checker' ) );

			// scripts for license screen.
			add_action( 'admin_footer', array( __CLASS__, 'load_scripts' ) );

			// expired license reminder on ticket list.
			add_action( 'admin_notices', array( __CLASS__, 'expired_notice' ) );
			add_action( 'wp_ajax_wpsc_addon_license_notice_dismiss', array( __CLASS__, 'dismiss_expired_notice' ) );
		}

		/**
		 * Register an add-on. Call it from the `wpsc_loaded` action.
		 *
		 * Arguments:
		 * slug    - key used in `wpsc-licenses` option (e.g. agentgroups).
		 * item_id - product id in the store.
		 * file    - add-on main plugin file (__FILE__).
		 * image   - optional; image file name in core asset/images (default {slug}.png).
		 * settings_url - optional; admin page (relative to wp-admin) for the "Settings" link on the plugins screen.
		 *
		 * @param array $args - add-on arguments.
		 * @return boolean
		 */
		public static function register( $args ) {

			$args = wp_parse_args(
				$args,
				array(
					'slug'         => '',
					'item_id'      => 0,
					'file'         => '',
					'image'        => '',
					'settings_url' => '',
				)
			);

			$slug = sanitize_key( $args['slug'] );
			if ( ! $slug || ! $args['item_id'] || ! $args['file'] ) {
				return false;
			}

			self::$addons[ $slug ] = array(
				'slug'     => $slug,
				'item_id'  => absint( $args['item_id'] ),
				'file'     => $args['file'],
				'basename' => plugin_basename( $args['file'] ),
				'image'    => $args['image'] ? $args['image'] : $slug . '.png',
			);

			if ( $args['settings_url'] ) {
				self::add_settings_link( self::$addons[ $slug ]['basename'], $args['settings_url'] );
			}

			if ( ! has_action( 'wpsc_licenses', array( __CLASS__, 'print_license_rows' ) ) ) {
				add_action( 'wpsc_licenses', array( __CLASS__, 'print_license_rows' ) );
			}

			// Notice on plugin row.
			add_action(
				'after_plugin_row_' . self::$addons[ $slug ]['basename'],
				function () use ( $slug ) {
					self::print_plugin_row_notice( $slug );
				}
			);

			return true;
		}

		/**
		 * Add "Settings" as the first action link of a plugin on the plugins screen
		 *
		 * @param string $basename     - plugin basename.
		 * @param string $settings_url - admin page url relative to wp-admin.
		 * @return void
		 */
		public static function add_settings_link( $basename, $settings_url ) {

			add_filter(
				'plugin_action_links_' . $basename,
				function ( $links ) use ( $settings_url ) {

					if ( ! current_user_can( 'manage_options' ) ) {
						return $links;
					}

					$link = '<a href="' . esc_url( admin_url( $settings_url ) ) . '">' . esc_html__( 'Settings', 'supportcandy' ) . '</a>';
					return array_merge( array( 'settings' => $link ), $links );
				}
			);
		}

		/**
		 * Get registered add-on along with header data (name, version) read from its main file
		 *
		 * @param string $slug - add-on slug.
		 * @return array|boolean
		 */
		public static function get_addon( $slug ) {

			if ( ! isset( self::$addons[ $slug ] ) ) {
				return false;
			}

			if ( ! isset( self::$addons[ $slug ]['name'] ) ) {
				$data                           = get_file_data(
					self::$addons[ $slug ]['file'],
					array(
						'name'    => 'Plugin Name',
						'version' => 'Version',
					),
					'plugin'
				);
				self::$addons[ $slug ]['name']    = $data['name'];
				self::$addons[ $slug ]['version'] = $data['version'];
			}

			return self::$addons[ $slug ];
		}

		/**
		 * Get license status of an add-on
		 *
		 * @param string $slug - add-on slug.
		 * @return array - key, expiry, status (none|active|lifetime|expired), expiry_date (DateTime|null)
		 */
		private static function get_license_status( $slug ) {

			$licenses = get_option( 'wpsc-licenses', array() );
			$license  = isset( $licenses[ $slug ] ) ? $licenses[ $slug ] : array();
			$key      = isset( $license['key'] ) ? $license['key'] : '';
			$expiry   = isset( $license['expiry'] ) ? $license['expiry'] : '';

			$status = array(
				'key'         => $key,
				'status'      => 'none',
				'expiry_date' => null,
			);
			if ( ! $key ) {
				return $status;
			}

			if ( 'lifetime' === $expiry ) {
				$status['status'] = 'lifetime';
				return $status;
			}

			try {
				$expiry_date = new DateTime( $expiry );
			} catch ( Exception $e ) {
				$status['status'] = 'active';
				return $status;
			}
			$status['expiry_date'] = $expiry_date;
			$status['status']      = new DateTime() < $expiry_date ? 'active' : 'expired';
			return $status;
		}

		/**
		 * Print license row for each registered add-on
		 *
		 * @return void
		 */
		public static function print_license_rows() {

			foreach ( array_keys( self::$addons ) as $slug ) {

				$addon   = self::get_addon( $slug );
				$license = self::get_license_status( $slug );
				?>
				<div class="license-container">

					<img src="<?php echo esc_url( WPSC_PLUGIN_URL . 'asset/images/' . $addon['image'] ); ?>" alt="<?php echo esc_attr( $addon['name'] ); ?>">
					<input type="text" value="<?php echo esc_attr( $license['key'] ); ?>" <?php echo $license['key'] ? 'disabled' : ''; ?> autocomplete="off"/>

					<?php if ( $license['key'] ) : ?>

						<button class="wpsc-button small primary" onclick="wpsc_addon_license_deactivate(this, '<?php echo esc_attr( $slug ); ?>');"><?php echo esc_html( wpsc__( 'Deactivate' ) ); ?></button>

						<?php if ( 'lifetime' === $license['status'] ) : ?>
							<p><?php echo esc_html( wpsc_translate_common_strings( 'license-activated' ) ); ?></p>
						<?php elseif ( 'expired' === $license['status'] ) : ?>
							<p class="expired">
								<?php
								echo esc_html( sprintf( wpsc_translate_common_strings( 'license-expired' ), $license['expiry_date']->format( 'F d, Y' ) ) );
								?>
							</p>
						<?php elseif ( $license['expiry_date'] ) : ?>
							<p>
								<?php
								echo esc_html( sprintf( wpsc_translate_common_strings( 'license-expires' ), $license['expiry_date']->format( 'F d, Y' ) ) );
								?>
							</p>
						<?php else : ?>
							<p><?php echo esc_html( wpsc_translate_common_strings( 'license-activated' ) ); ?></p>
						<?php endif; ?>

					<?php else : ?>

						<button class="wpsc-button small primary" onclick="wpsc_addon_license_activate(this, '<?php echo esc_attr( $slug ); ?>');"><?php echo esc_html( wpsc__( 'Activate' ) ); ?></button>
						<p><?php esc_html_e( 'Enter your license key to receive updates and security fixes.', 'supportcandy' ); ?></p>

					<?php endif; ?>
				</div>
				<?php
			}
		}

		/**
		 * Print notice below the add-on row in plugins list when license is missing or expired
		 *
		 * @param string $slug - add-on slug.
		 * @return void
		 */
		private static function print_plugin_row_notice( $slug ) {

			$addon   = self::get_addon( $slug );
			$license = self::get_license_status( $slug );
			if ( ! in_array( $license['status'], array( 'none', 'expired' ), true ) ) {
				return;
			}

			$colspan = _get_list_table( 'WP_Plugins_List_Table' )->get_column_count();
			?>
			<style>
				.plugins tr[data-plugin="<?php echo esc_attr( $addon['basename'] ); ?>"] th,
				.plugins tr[data-plugin="<?php echo esc_attr( $addon['basename'] ); ?>"] td {
					box-shadow: none;
				}
			</style>
			<tr class="plugin-update-tr active">
				<td colspan="<?php echo esc_attr( $colspan ); ?>" class="plugin-update colspanchange">
					<div class="update-message notice inline notice-warning notice-alt">
						<p>
							<?php if ( 'expired' === $license['status'] ) : ?>
								<?php esc_html_e( 'Your license key has expired. Renew it to receive updates and security fixes.', 'supportcandy' ); ?>
							<?php else : ?>
								<?php esc_html_e( 'Enter your license key to receive updates and security fixes.', 'supportcandy' ); ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpsc-license' ) ); ?>"><?php esc_html_e( 'Enter license key', 'supportcandy' ); ?></a>
							<?php endif; ?>
						</p>
					</div>
				</td>
			</tr>
			<?php
		}

		/**
		 * Reminder on ticket list that licenses are expired. Dismissing it hides it for one day.
		 *
		 * @return void
		 */
		public static function expired_notice() {

			if ( ! self::$addons || ! isset( $_REQUEST['page'] ) || 'wpsc-tickets' !== $_REQUEST['page'] || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore
				return;
			}

			$dismissed = (int) get_user_meta( get_current_user_id(), 'wpsc_license_notice_dismissed', true );
			if ( time() - $dismissed < DAY_IN_SECONDS ) {
				return;
			}

			$has_expired = false;
			foreach ( array_keys( self::$addons ) as $slug ) {
				if ( 'expired' === self::get_license_status( $slug )['status'] ) {
					$has_expired = true;
					break;
				}
			}
			if ( ! $has_expired ) {
				return;
			}
			?>
			<div class="notice notice-warning is-dismissible wpsc-license-expired-notice">
				<p>
					<?php esc_html_e( 'Your licenses have expired. Add-ons with an expired license do not receive updates and security fixes.', 'supportcandy' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpsc-license' ) ); ?>"><?php esc_html_e( 'View licenses', 'supportcandy' ); ?></a>
				</p>
			</div>
			<script>
				jQuery(document).on('click', '.wpsc-license-expired-notice .notice-dismiss', function () {
					jQuery.post(ajaxurl, {
						action: 'wpsc_addon_license_notice_dismiss',
						_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_addon_license' ) ); ?>'
					});
				});
			</script>
			<?php
		}

		/**
		 * Remember that current user dismissed expired license reminder
		 *
		 * @return void
		 */
		public static function dismiss_expired_notice() {

			WPSC_Ajax::guard( 'wpsc_addon_license' );
			update_user_meta( get_current_user_id(), 'wpsc_license_notice_dismissed', time() );
			wp_send_json( array( 'success' => true ) );
		}

		/**
		 * Send request to the store for license action
		 *
		 * @param string  $edd_action - activate_license|deactivate_license|check_license.
		 * @param string  $key        - license key.
		 * @param integer $item_id    - store product id.
		 * @return array|WP_Error - decoded response
		 */
		private static function store_request( $edd_action, $key, $item_id ) {

			$response = wp_remote_post(
				WPSC_STORE_URL,
				array(
					'body'      => array(
						'edd_action' => $edd_action,
						'license'    => $key,
						'item_id'    => $item_id,
						'url'        => home_url(),
					),
					'timeout'   => 20,
					'sslverify' => true,
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $code ) {
				return new WP_Error(
					'http',
					/* translators: %d: HTTP response code returned by the license server */
					sprintf( __( 'Could not reach the license server (HTTP %d). This may be caused by a firewall or security plugin blocking outgoing requests. Please try again later or contact support.', 'supportcandy' ), $code )
				);
			}

			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $data ) ) {
				return new WP_Error( 'invalid', __( 'Invalid response from license server.', 'supportcandy' ) );
			}

			return $data;
		}

		/**
		 * Read and validate slug and license key from ajax request
		 *
		 * @return array - slug, key, addon
		 */
		private static function get_request_data() {

			$slug = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : ''; // phpcs:ignore
			$key  = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : ''; // phpcs:ignore

			if ( ! $key || ! isset( self::$addons[ $slug ] ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => __( 'Bad request!', 'supportcandy' ),
					)
				);
			}

			return array( $slug, $key, self::$addons[ $slug ] );
		}

		/**
		 * Activate license
		 *
		 * @return void
		 */
		public static function activate() {

			WPSC_Ajax::guard( 'wpsc_addon_license' );
			list( $slug, $key, $addon ) = self::get_request_data();

			$data = self::store_request( 'activate_license', $key, $addon['item_id'] );
			if ( is_wp_error( $data ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => $data->get_error_message(),
					)
				);
			}

			if ( empty( $data['success'] ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => isset( $data['error'] ) ? wpsc_licence_errors( $data['error'] ) : __( 'Could not activate license', 'supportcandy' ),
					)
				);
			}

			$licenses          = get_option( 'wpsc-licenses', array() );
			$licenses[ $slug ] = array(
				'key'    => $key,
				'expiry' => $data['expires'],
			);
			update_option( 'wpsc-licenses', $licenses );

			wp_send_json(
				array(
					'success' => true,
					'message' => '',
				)
			);
		}

		/**
		 * Deactivate license. Store is notified on a best-effort basis; the key is always removed locally regardless of the store response.
		 *
		 * @return void
		 */
		public static function deactivate() {

			WPSC_Ajax::guard( 'wpsc_addon_license' );
			list( $slug, $key, $addon ) = self::get_request_data();

			// Best effort. Result is intentionally ignored (non-200, errors, refusals).
			self::store_request( 'deactivate_license', $key, $addon['item_id'] );

			$licenses = get_option( 'wpsc-licenses', array() );
			unset( $licenses[ $slug ] );
			update_option( 'wpsc-licenses', $licenses );

			wp_send_json(
				array(
					'success' => true,
					'message' => '',
				)
			);
		}

		/**
		 * Daily license synchronization with the store.
		 * All registered add-ons are checked in parallel with short timeout.
		 *
		 * @return void
		 */
		public static function license_checker() {

			$licenses = get_option( 'wpsc-licenses', array() );
			$requests = array();
			foreach ( self::$addons as $slug => $addon ) {
				if ( empty( $licenses[ $slug ]['key'] ) ) {
					continue;
				}
				$requests[ $slug ] = array(
					'edd_action' => 'check_license',
					'license'    => $licenses[ $slug ]['key'],
					'item_id'    => $addon['item_id'],
					'url'        => home_url(),
				);
			}
			if ( ! $requests ) {
				return;
			}

			$bodies  = self::check_licenses_parallel( $requests );
			$changed = false;
			foreach ( $bodies as $slug => $body ) {
				$data = json_decode( $body, true );
				if ( ! empty( $data['success'] ) && isset( $data['expires'] ) && $data['expires'] != $licenses[ $slug ]['expiry'] ) { // phpcs:ignore
					$licenses[ $slug ]['expiry'] = $data['expires'];
					$changed                     = true;
				}
			}
			if ( $changed ) {
				update_option( 'wpsc-licenses', $licenses );
			}
		}

		/**
		 * Send license check requests concurrently
		 *
		 * @param array $params - post body for each request indexed by add-on slug.
		 * @return array - response body for successful requests indexed by add-on slug.
		 */
		private static function check_licenses_parallel( $params ) {

			$timeout = 5;
			$bodies  = array();

			// WP >= 6.2 ships namespaced Requests library; older versions have global one.
			$requests_class = class_exists( '\WpOrg\Requests\Requests' ) ? '\WpOrg\Requests\Requests' : ( class_exists( 'Requests' ) ? 'Requests' : '' );

			if ( $requests_class ) {

				$requests = array();
				foreach ( $params as $slug => $body ) {
					$requests[ $slug ] = array(
						'url'  => WPSC_STORE_URL,
						'type' => 'POST',
						'data' => $body,
					);
				}

				try {
					$responses = $requests_class::request_multiple(
						$requests,
						array(
							'timeout'         => $timeout,
							'connect_timeout' => $timeout,
							'useragent'       => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
							'verify'          => ABSPATH . WPINC . '/certificates/ca-bundle.crt',
						)
					);
					foreach ( $responses as $slug => $response ) {
						if ( is_object( $response ) && isset( $response->status_code ) && 200 === (int) $response->status_code ) {
							$bodies[ $slug ] = $response->body;
						}
					}
					return $bodies;
				} catch ( Throwable $e ) {
					$bodies = array();
				}
			}

			// Fallback: sequential requests with short timeout; stop at first connection failure.
			foreach ( $params as $slug => $body ) {
				$response = wp_remote_post(
					WPSC_STORE_URL,
					array(
						'body'      => $body,
						'timeout'   => $timeout,
						'sslverify' => true,
					)
				);
				if ( is_wp_error( $response ) ) {
					break;
				}
				if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
					$bodies[ $slug ] = wp_remote_retrieve_body( $response );
				}
			}
			return $bodies;
		}

		/**
		 * Scripts for license screen
		 *
		 * @return void
		 */
		public static function load_scripts() {

			if ( ! self::$addons || ! isset( $_REQUEST['page'] ) || 'wpsc-license' !== $_REQUEST['page'] ) { // phpcs:ignore
				return;
			}
			?>
			<script>
				function wpsc_addon_license_request(el, action, slug) {

					var licenseKey = jQuery(el).prev().val().trim();
					if (!licenseKey) return;

					var originalText = jQuery(el).text();
					jQuery(el).text(supportcandy.translations.please_wait);
					jQuery.post(supportcandy.ajax_url, {
						action: action,
						slug: slug,
						license_key: licenseKey,
						_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_addon_license' ) ); ?>'
					})
					.done(function (response) {
						if (response && response.success) {
							window.location.reload();
							return;
						}
						jQuery(el).text(originalText);
						alert(response && response.message ? response.message : supportcandy.translations.something_wrong);
					})
					.fail(function () {
						jQuery(el).text(originalText);
						alert(supportcandy.translations.something_wrong);
					});
				}
				function wpsc_addon_license_activate(el, slug) {
					wpsc_addon_license_request(el, 'wpsc_addon_license_activate', slug);
				}
				function wpsc_addon_license_deactivate(el, slug) {
					wpsc_addon_license_request(el, 'wpsc_addon_license_deactivate', slug);
				}
			</script>
			<?php
		}

		/**
		 * List of all supportcandy add-ons
		 *
		 * @return void
		 */
		public static function layout() {
			?>

			<style>
				@import url('https://fonts.googleapis.com/css2?family=Nunito&display=swap');

				body {
					font-family: 'Nunito', sans-serif;
					margin: 0;
				}

				.header {
					position: relative;
				}

				.inner-header {
					height: 100%;
					width: 100%;
					margin: 0;
					padding: 0;
				}

				.flex {
					/*Flexbox for containers*/
					justify-content: center;
					align-items: center;
					text-align: center;
				}

				section {
					min-height: 100%;
					position: relative;
					z-index: 10;
				}

				@media (min-width: 320px) and (max-width: 768px) {

					.header-title {
						font-size: 2.2em;
						color: #2C3E50;
						line-height: 1.1em;
					}

					.header-title-main {
						font-size: 3em;
						color: #2C3E50;
						line-height: 1.1em;
					}

					.header-subtitle {
						font-size: 1.5em;
						color: #171717;
						text-align: left;
						margin: 0em 0 1.4em 0;
					}

					.header-subtitle-main {
						font-size: 1.3em;
						color: #171717;
						margin: 0px 1em 3em 1em;
					}
				}

				@media (min-width: 769px) and (max-width: 1024px) {

					.header-title {
						font-size: 2.2em;;
						color: #2C3E50;
					}

					.header-title-main {
						font-size: 3em;
						color: #2C3E50;
					}

					.header-subtitle {
						font-size: 1.3em;
						color: #171717;
						text-align: left;
						margin: 0em 0 1.4em 0;
					}

					.header-subtitle-main {
						font-size: 1.5em;
						color: #171717;
						margin: 0px 1em 3em 1em;
					}
				}

				@media (min-width: 1025px) and (max-width: 1200px) {

					.header-title {
						font-size: 2.2em;;
						color: #2C3E50;
					}

					.header-title-main {
						font-size: 3em;
						color: #2C3E50;
					}

					.header-subtitle {
						font-size: 1.5em;
						color: #171717;
						text-align: left;
						margin: 0em 0 1.4em 0;
					}

					.header-subtitle-main {
						font-size: 1.3em;
						color: #171717;
						margin: 0px 1em 3em 1em;
					}
				}

				@media (min-width: 1201px) {

					.header-title-main {
						font-size: 3em;
						color: #2C3E50;
						margin: 1.2em 0 0.67em 0;
					}

					.header-title {
						font-size: 2.2em;
						color: #2C3E50;
						margin: 2em 0 0.4em 0;
						text-align: left;
					}

					.header-subtitle {
						font-size: 1.3em;
						color: #171717;
						text-align: left;
						margin: 0em 0 1.4em 0;
					}

					.header-subtitle-main {
						font-size: 1.5em;
						color: #171717;
						margin: 0px 1em 3em 1em;
					}
				}

				@keyframes move-forever {
					0% {
						transform: translate3d(-90px, 0, 0);
					}

					100% {
						transform: translate3d(85px, 0, 0);
					}
				}
				.wpsc-licenses-container button {
					margin-top: 1em;
				}
				.wpsc-licenses-container a {
					text-decoration: none;
					color: #3c434a;
				}
				.wpsc-licenses-container .license-container {
					cursor: pointer;
				}
				.wpsc-licenses-container .license-container:hover {
					-webkit-transform: scale(1.04);
					transform: scale(1.04);
					-webkit-transition: 0.2s ease;
					transition: 0.2s ease;
				}
				.wpsc-licenses-container .license-container {
					-webkit-transition: 0.2s ease;
					transition: 0.2s ease;
				}
			</style>

			<body>
				<section>
					<div class="header">
						<div class="inner-header">
							<div class="flex">
								<h1 class="header-title-main">Simple, straight forward pricing.</h1>
								<p class="header-subtitle-main">We believe in providing honest and fair pricing. Purchase in confidence with our 14-day money-back guarantee.</p>
							</div>
						</div>
					</div>
					<div class="header">
						<div class="inner-header">
							<div class="flex">
								<h1 class="header-title">Extensions</h1>
								<p class="header-subtitle">Supercharge your ticket system with our premium extensions available as add-ons so that you install them only if needed.</p>
							</div>
						</div>
					</div>
					<div id="wpsc-container">
						<div class="wpsc-licenses-container">
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/email-piping')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/email-piping.png' ); ?>" alt="">
								<p>Allows customers and agents to create and reply to tickets directly from their email inboxes.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/workflows')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/workflows.png' ); ?>" alt="">
								<p>Unlock the power of automation with SupportCandy workflows, revolutionizing the way you manage your processes.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/sla')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/sla.png' ); ?>" alt="">
								<p>You can offer and track the time you take to respond to and resolve different types of incoming tickets from customers.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/usergroups')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/usergroups.png' ); ?>" alt="">
								<p>You can create a group of users or companies so that the company’s supervisor can manage all tickets created by the group members.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/agentgroups')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/agentgroups.png' ); ?>" alt="">
								<p>You can create groups of agents to assign tickets just like individual agents. The supervisor of the group can assign tickets to his team members.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/satisfaction-survey')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/satisfaction-survey.png' ); ?>" alt="">
								<p>Collect customer feedback and rating for each ticket. This helps you understand how your team performs.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/timer')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/timer.png' ); ?>" alt="">
								<p>Allows your agents to separately record the time spent on each ticket in the form of a stopwatch.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/private-credentials')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/private-credentials.png' ); ?>" alt="">
								<p>Allows your customers to share sensitive information within the ticket so that it is visible to only agents with permission.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/schedule-tickets')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/schedule-tickets.png' ); ?>" alt="">
								<p>Automatically create periodic tickets by setting recurring time and information.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/canned-reply')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/canned-reply.png' ); ?>" alt="">
								<p>Agents can save their replies which can be accessed in just a few clicks while replying to the tickets.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/automatic-close-tickets')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/automatic-close-tickets.png' ); ?>" alt="">
								<p>Automatically close the ticket after x days of inactivity. You can also send an inactivity warning email to the customer before x days of closing the ticket.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/reports')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/reports.png' ); ?>" alt="">
								<p>Measure and improve the efficiency of your support using our advanced reporting.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/export-tickets')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/export-tickets.png' ); ?>" alt="">
								<p>Export tickets in CSV format as per the current filter from the ticket list page.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/print-ticket')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/print-ticket.png' ); ?>" alt="">
								<p>Add print ticket feature to SupportCandy using custom templates.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/assign-agent-rules')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/assign-agent-rules.png' ); ?>" alt="">
								<p>Conditionally assign agents to new tickets automatically using set rules and workload.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/productivity-suite/')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/productivity-suite.png' ); ?>" alt="">
								<p>The productivity suite is a collection of features designed to help agents/users perform a variety of tasks more efficiently and effectively.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/webhooks/')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/webhooks.png' ); ?>" alt="">
								<p>Webhooks deliver real-time notifications, ensuring you stay informed during critical support events, from new ticket creation to closures and agent assignments. Tailor your support experience with dynamic, customizable alerts.</p>
							</div>
						</div>
					</div>
					<div class="header">
						<div class="inner-header">
							<div class="flex">
								<h1 class="header-title">Integrations</h1>
								<p class="header-subtitle">Seamlessly connect all your favorite tools and streamline your workflow with our powerful integrations.</p>
							</div>
						</div>
					</div>
					<div id="wpsc-container">
						<div class="wpsc-licenses-container">
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/woocommerce-integration')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/woocommerce.png' ); ?>" alt="">
								<p>Allows your customers to choose orders and products within the ticket form. Also, allows your agents to view customer orders within the ticket.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/slack-integration/')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/slack.png' ); ?>" alt="">
								<p>Get instant notifications to your Slack Channel and respond directly from Slack thread reply.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/gravity-forms-integration')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/gravity-forms.png' ); ?>" alt="">
								<p>Integrate Gravity Forms with SupportCandy and allows you to create multiple ticket forms.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/edd-integration')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/edd.png' ); ?>" alt="">
								<p>Allows your customers to choose orders and products within the ticket form. Also, allows your agents to view customer orders within the ticket.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/faq-integrations')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/faq-integrations.png' ); ?>" alt="">
								<p>Intergrates popular FAQ plugins with SupportCandy.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/knoledgebase-integrations')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/knowledgebase-integrations.png' ); ?>" alt="">
								<p>Integrates popular knowledge-base plugins to SupportCandy.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/lms-integration')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/lms-integration.png' ); ?>" alt="">
								<p>Integrates popular LMS plugins to SupportCandy.</p>
							</div>
							<div class="license-container" onclick="window.open('https://supportcandy.net/downloads/email-marketing-tools-integration/')">
								<img src="<?php echo esc_url( WPSC_PLUGIN_URL . '/asset/images/marketing-tool.png' ); ?>" alt="">
								<p>Elevate customer engagement by connecting SupportCandy with leading email marketing tools like MailChimp, GetResponse, and Brevo.</p>
							</div>
						</div>
					</div>
				</section>
			</body>
			<?php
		}
	}
endif;

WPSC_Addons::init();
