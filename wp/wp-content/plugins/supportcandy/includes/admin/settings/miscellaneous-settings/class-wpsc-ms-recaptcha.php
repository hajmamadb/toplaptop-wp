<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_MS_Recaptcha' ) ) :

	final class WPSC_MS_Recaptcha {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// User interface.
			add_action( 'wp_ajax_wpsc_get_ms_recaptcha', array( __CLASS__, 'load_settings_ui' ) );
			add_action( 'wp_ajax_wpsc_set_ms_recaptcha', array( __CLASS__, 'save_settings' ) );
			add_action( 'wp_ajax_wpsc_reset_ms_recaptcha', array( __CLASS__, 'reset_settings' ) );

			// Print in create ticket form and my profile.
			add_action( 'wpsc_print_tff', array( __CLASS__, 'print_tff' ), 9 );
			add_action( 'wpsc_my_profile', array( __CLASS__, 'print_tff' ), 9 );

			// TFF! validation.
			add_action( 'wpsc_js_validate_ticket_form', array( __CLASS__, 'js_validate_ticket_form' ) );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			$recaptcha = apply_filters(
				'wpsc_recaptcha_settings',
				array(
					'captcha-provider'      => '0',
					'recaptcha-version'     => 3,
					'recaptcha-site-key'    => '',
					'recaptcha-secret-key'  => '',
					'cloudflare-site-key'   => '',
					'cloudflare-secret-key' => '',
				)
			);
			update_option( 'wpsc-recaptcha-settings', $recaptcha );
		}

		/**
		 * Settings user interface
		 *
		 * @return void
		 */
		public static function load_settings_ui() {

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			$settings = get_option( 'wpsc-recaptcha-settings', array() );?>

			<form action="#" onsubmit="return false;" class="wpsc-frm-ms-recaptcha">
				<div class="wpsc-input-group">
					<div class="label-container">
						<label for="wpsc-captcha-provider"><?php esc_attr_e( 'CAPTCHA Provider', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Adds a CAPTCHA check to SupportCandy forms such as login, registration and create ticket to block spam bots. Choose Disable to turn it off.', 'supportcandy' ); ?></p>
					<select id="wpsc-captcha-provider" name="captcha-provider">
						<option <?php selected( $settings['captcha-provider'], '0' ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
						<option <?php selected( $settings['captcha-provider'], 'google-recaptcha' ); ?> value="google-recaptcha"><?php esc_attr_e( 'Google reCAPTCHA', 'supportcandy' ); ?></option>
						<option <?php selected( $settings['captcha-provider'], 'cloudflare-turnstile' ); ?> value="cloudflare-turnstile"><?php esc_attr_e( 'Cloudflare Turnstile', 'supportcandy' ); ?></option>
					</select>
				</div>
				<div class="wpsc-input-group" id="wpsc-ms-recaptcha-version">
					<div class="label-container">
						<label for="wpsc-recaptcha-version"><?php esc_attr_e( 'reCaptcha version', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Version 2 shows the "I\'m not a robot" checkbox, while version 3 runs in the background with no checkbox. Use keys created for the selected version.', 'supportcandy' ); ?></p>
					<select id="wpsc-recaptcha-version" name="recaptcha-version">
						<option <?php selected( $settings['recaptcha-version'], '2' ); ?> value="2">2</option>
						<option <?php selected( $settings['recaptcha-version'], '3' ); ?> value="3">3</option>
					</select>
				</div>

				<div class="wpsc-input-group" id="wpsc-ms-recaptcha-site-key">
					<div class="label-container">
						<label for="wpsc-recaptcha-site-key"><?php esc_attr_e( 'Site key', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Site key from your Google reCAPTCHA admin console. Your site\'s domain must be added to this key.', 'supportcandy' ); ?></p>
					<input type="text" id="wpsc-recaptcha-site-key" name="recaptcha-site-key" value="<?php echo esc_attr( $settings['recaptcha-site-key'] ); ?>" autocomplete="off">
				</div>
				<div class="wpsc-input-group" id="wpsc-ms-recaptcha-secret-key">
					<div class="label-container">
						<label for="wpsc-recaptcha-secret-key"><?php esc_attr_e( 'Secret key', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Secret key from your Google reCAPTCHA admin console. It is used on the server to verify each CAPTCHA response.', 'supportcandy' ); ?></p>
					<input type="text" id="wpsc-recaptcha-secret-key" name="recaptcha-secret-key" value="<?php echo esc_attr( $settings['recaptcha-secret-key'] ); ?>" autocomplete="off">
				</div>

				 
				<div class="wpsc-input-group" id="wpsc-ms-cloudflare-site-key">
					<div class="label-container">
						<label for="wpsc-cloudflare-site-key"><?php esc_attr_e( 'Site key', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Site key from your Cloudflare Turnstile dashboard.', 'supportcandy' ); ?></p>
					<input type="text" id="wpsc-cloudflare-site-key" name="cloudflare-site-key" value="<?php echo esc_attr( $settings['cloudflare-site-key'] ); ?>" autocomplete="off">
				</div>
				<div class="wpsc-input-group" id="wpsc-ms-cloudflare-secret-key">
					<div class="label-container">
						<label for="wpsc-cloudflare-secret-key"><?php esc_attr_e( 'Secret key', 'supportcandy' ); ?></label>
					</div>
					<p class="wpsc-input-desc"><?php esc_attr_e( 'Secret key from your Cloudflare Turnstile dashboard. It is used on the server to verify each CAPTCHA response.', 'supportcandy' ); ?></p>
					<input type="text" id="wpsc-cloudflare-secret-key" name="cloudflare-secret-key" value="<?php echo esc_attr( $settings['cloudflare-secret-key'] ); ?>" autocomplete="off">
				</div>

				<script>
					jQuery( '#wpsc-captcha-provider' ).change( function() {

						var provider = jQuery( this ).val();
						if ( provider === 'google-recaptcha' ) {
							jQuery( '#wpsc-ms-recaptcha-allow, #wpsc-ms-recaptcha-version, #wpsc-ms-recaptcha-site-key, #wpsc-ms-recaptcha-secret-key' ).show();
							jQuery( '#wpsc-ms-cloudflare-site-key, #wpsc-ms-cloudflare-secret-key' ).hide();
						} else if ( provider === 'cloudflare-turnstile' ) {
							jQuery( '#wpsc-ms-recaptcha-allow, #wpsc-ms-recaptcha-version, #wpsc-ms-recaptcha-site-key, #wpsc-ms-recaptcha-secret-key' ).hide();
							jQuery( '#wpsc-ms-cloudflare-site-key, #wpsc-ms-cloudflare-secret-key' ).show();
						} else {
							jQuery( '#wpsc-ms-recaptcha-allow, #wpsc-ms-recaptcha-version, #wpsc-ms-recaptcha-site-key, #wpsc-ms-recaptcha-secret-key' ).hide();
							jQuery( '#wpsc-ms-cloudflare-site-key, #wpsc-ms-cloudflare-secret-key' ).hide();
						}
					} );
					jQuery( '#wpsc-captcha-provider' ).trigger( 'change' );
				</script>

				<?php do_action( 'wpsc_ms_recaptcha' ); ?>
				<input type="hidden" name="action" value="wpsc_set_ms_recaptcha">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_ms_recaptcha' ) ); ?>">
			</form>
			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_ms_recaptcha(this);">
					<?php esc_attr_e( 'Submit', 'supportcandy' ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_reset_ms_recaptcha(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_ms_recaptcha' ) ); ?>');">
					<?php esc_attr_e( 'Reset default', 'supportcandy' ); ?></button>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Save settings
		 *
		 * @return void
		 */
		public static function save_settings() {

			if ( check_ajax_referer( 'wpsc_set_ms_recaptcha', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$captcha_provider = isset( $_POST['captcha-provider'] ) ? sanitize_text_field( wp_unslash( $_POST['captcha-provider'] ) ) : '0';
			if ( ! in_array( $captcha_provider, array( 'google-recaptcha', 'cloudflare-turnstile', '0' ), true ) ) {
				$captcha_provider = '0';
			}

			$posted_version = isset( $_POST['recaptcha-version'] ) ? intval( $_POST['recaptcha-version'] ) : 3;
			$keys           = array();
			if ( $captcha_provider === 'google-recaptcha' ) {
				$keys = array(
					'site'   => 'recaptcha-site-key',
					'secret' => 'recaptcha-secret-key',
				);
			} elseif ( $captcha_provider === 'cloudflare-turnstile' ) {
				$keys = array(
					'site'   => 'cloudflare-site-key',
					'secret' => 'cloudflare-secret-key',
				);
			}

			$warning = '';
			if ( $keys ) {

				$site_key   = isset( $_POST[ $keys['site'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $keys['site'] ] ) ) : '';
				$secret_key = isset( $_POST[ $keys['secret'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $keys['secret'] ] ) ) : '';

				if ( ! $site_key ) {
					wp_send_json_error(
						array(
							'field'   => $keys['site'],
							'message' => __( 'Site key is required.', 'supportcandy' ),
						)
					);
				}
				if ( ! $secret_key ) {
					wp_send_json_error(
						array(
							'field'   => $keys['secret'],
							'message' => __( 'Secret key is required.', 'supportcandy' ),
						)
					);
				}

				if ( $site_key === $secret_key ) {
					wp_send_json_error(
						array(
							'field'   => $keys['secret'],
							'message' => __( 'Site key and secret key must be different. Copy both keys again from the CAPTCHA provider dashboard.', 'supportcandy' ),
						)
					);
				}

				$check = self::check_site_key( $captcha_provider, $site_key );
				if ( $check === 'invalid' ) {
					wp_send_json_error(
						array(
							'field'   => $keys['site'],
							'message' => __( 'Site key is not valid.', 'supportcandy' ),
						)
					);
				} elseif ( $check === 'invalid-domain' ) {
					wp_send_json_error(
						array(
							'field'   => $keys['site'],
							/* translators: %s: site domain */
							'message' => sprintf( __( 'This site key is not allowed on %s. Add this domain to the key in the Google reCAPTCHA admin console.', 'supportcandy' ), wp_parse_url( home_url(), PHP_URL_HOST ) ),
						)
					);
				}
				$unverified = $check === 'unknown';

				$check = self::check_secret_key( $captcha_provider, $secret_key );
				if ( $check === 'invalid' ) {
					wp_send_json_error(
						array(
							'field'   => $keys['secret'],
							'message' => __( 'Secret key is not valid.', 'supportcandy' ),
						)
					);
				}
				if ( $unverified || $check === 'unknown' ) {
					$warning = __( 'Settings saved, but the keys could not be verified because the verification service could not be reached.', 'supportcandy' );
				}
			}

			$recaptcha = apply_filters(
				'wpsc_set_recaptcha',
				array(
					'captcha-provider'      => $captcha_provider,
					'recaptcha-version'     => isset( $_POST['recaptcha-version'] ) ? $posted_version : '3',
					'recaptcha-site-key'    => isset( $_POST['recaptcha-site-key'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha-site-key'] ) ) : '',
					'recaptcha-secret-key'  => isset( $_POST['recaptcha-secret-key'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha-secret-key'] ) ) : '',
					'cloudflare-site-key'   => isset( $_POST['cloudflare-site-key'] ) ? sanitize_text_field( wp_unslash( $_POST['cloudflare-site-key'] ) ) : '',
					'cloudflare-secret-key' => isset( $_POST['cloudflare-secret-key'] ) ? sanitize_text_field( wp_unslash( $_POST['cloudflare-secret-key'] ) ) : '',
				)
			);
			update_option( 'wpsc-recaptcha-settings', $recaptcha );
			wp_send_json_success( array( 'warning' => $warning ) );
		}

		/**
		 * Check site key.
		 * Google: asks the reCAPTCHA anchor page (what the widget loads) for this key and site origin.
		 * Cloudflare has no such endpoint, so only the key format is checked.
		 *
		 * @param string $provider - captcha provider.
		 * @param string $site_key - site key.
		 * @return string - valid | invalid | invalid-domain | unknown (service could not be reached or answer not understood).
		 */
		public static function check_site_key( $provider, $site_key ) {

			if ( $provider === 'cloudflare-turnstile' ) {
				return preg_match( '/^[0-3]x[A-Za-z0-9_-]+$/', $site_key ) ? 'valid' : 'invalid';
			}

			if ( ! preg_match( '/^[A-Za-z0-9_-]{40}$/', $site_key ) ) {
				return 'invalid';
			}

			$url    = wp_parse_url( home_url() );
			$scheme = isset( $url['scheme'] ) ? $url['scheme'] : 'https';
			$port   = isset( $url['port'] ) ? $url['port'] : ( $scheme === 'https' ? 443 : 80 );
			$origin = $scheme . '://' . $url['host'] . ':' . $port;

			$response = wp_remote_get(
				add_query_arg(
					array(
						'ar'   => 1,
						'k'    => $site_key,
						'co'   => strtr( base64_encode( $origin ), '+/=', '-_.' ), // phpcs:ignore
						'hl'   => 'en',
						'size' => 'normal',
					),
					'https://www.google.com/recaptcha/api2/anchor'
				),
				array( 'timeout' => 10 )
			);
			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
				return 'unknown';
			}

			$body = wp_remote_retrieve_body( $response );
			if ( strpos( $body, 'Invalid domain for site key' ) !== false ) {
				return 'invalid-domain';
			}
			if ( strpos( $body, 'Invalid site key' ) !== false ) {
				return 'invalid';
			}
			return strpos( $body, 'recaptcha-token' ) !== false ? 'valid' : 'unknown';
		}

		/**
		 * Check secret key.
		 * Cloudflare: siteverify call using a dummy token, which reports a wrong secret before the token.
		 * Google checks the token first, so a wrong secret can't be detected without a solved CAPTCHA; only the key format is checked.
		 *
		 * @param string $provider - captcha provider.
		 * @param string $secret   - secret key.
		 * @return string - valid | invalid | unknown (service could not be reached or answer not understood).
		 */
		public static function check_secret_key( $provider, $secret ) {

			if ( $provider === 'google-recaptcha' ) {
				return preg_match( '/^[A-Za-z0-9_-]{40}$/', $secret ) ? 'valid' : 'invalid';
			}

			$response = wp_remote_post(
				'https://challenges.cloudflare.com/turnstile/v0/siteverify',
				array(
					'timeout' => 10,
					'body'    => array(
						'secret'   => $secret,
						'response' => 'wpsc-key-check',
					),
				)
			);
			if ( is_wp_error( $response ) ) {
				return 'unknown';
			}

			$result = json_decode( wp_remote_retrieve_body( $response ), true );
			$codes  = is_array( $result ) && isset( $result['error-codes'] ) ? (array) $result['error-codes'] : array();
			if ( in_array( 'invalid-input-secret', $codes, true ) || in_array( 'missing-input-secret', $codes, true ) ) {
				return 'invalid';
			}
			if ( ( is_array( $result ) && ! empty( $result['success'] ) ) || in_array( 'invalid-input-response', $codes, true ) ) {
				return 'valid';
			}
			return 'unknown';
		}

		/**
		 * Reset settings to default
		 *
		 * @return void
		 */
		public static function reset_settings() {

			if ( check_ajax_referer( 'wpsc_reset_ms_recaptcha', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			self::reset();
			wp_die();
		}

		/**
		 * Print ticket form field
		 *
		 * @return void
		 */
		public static function print_tff() {

			$recaptcha = get_option( 'wpsc-recaptcha-settings' );
			if ( $recaptcha['captcha-provider'] === 'google-recaptcha' && $recaptcha['recaptcha-version'] == 2 && $recaptcha['recaptcha-site-key'] && $recaptcha['recaptcha-secret-key'] ) {
				?>
				<script src="https://www.google.com/recaptcha/api.js" async defer></script> <?php // phpcs:ignore ?>
				<div class="wpsc-tff recaptcha wpsc-xs-12 wpsc-sm-12 wpsc-md-12 wpsc-lg-12 required wpsc-visible" data-cft="recaptcha">
					<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $recaptcha['recaptcha-site-key'] ); ?>"></div>
				</div>
				<?php
			}
			if ( $recaptcha['captcha-provider'] === 'google-recaptcha' && $recaptcha['recaptcha-version'] == 3 && $recaptcha['recaptcha-site-key'] && $recaptcha['recaptcha-secret-key'] ) {
				?>
				<script src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr( $recaptcha['recaptcha-site-key'] ); ?>"></script> <?php // phpcs:ignore ?>
				<?php
			}
			if ( $recaptcha['captcha-provider'] === 'cloudflare-turnstile' && $recaptcha['cloudflare-site-key'] && $recaptcha['cloudflare-secret-key'] ) {
				?>
				<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script> <?php // phpcs:ignore ?>
				<div class="wpsc-tff turnstile wpsc-xs-12 wpsc-sm-12 wpsc-md-12 wpsc-lg-12 required wpsc-visible" data-cft="turnstile">
					<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $recaptcha['cloudflare-site-key'] ); ?>"></div>
				</div>
				<script>
					jQuery(document).ready(function() {

						function wpscInitTurnstileWidgets() {

							if (
								typeof window.turnstile === 'undefined' ||
								typeof window.turnstile.render !== 'function'
							) {
								return false;
							}

							jQuery('.cf-turnstile').each(function() {

								var widget = jQuery(this);

								if (
									widget.find('input[name="cf-turnstile-response"]').length ||
									widget.find('iframe').length ||
									widget.attr('data-wpsc-rendered') === '1'
								) {

									widget.attr('data-wpsc-rendered', '1');

									return;
								}

								window.turnstile.render(
									this,
									{
										sitekey: widget.data('sitekey')
									}
								);

								widget.attr('data-wpsc-rendered', '1');
							});

							return true;
						}

						if (wpscInitTurnstileWidgets()) {
							return;
						}

						var attempts = 0;

						var timer = setInterval(
							function() {

								attempts++;

								if (
									wpscInitTurnstileWidgets() ||
									attempts > 50
								) {

									clearInterval(timer);
								}

							},
							100
						);

					});
				</script>
				<?php
			}
		}

		/**
		 * Validate this type field in create ticket
		 *
		 * @return void
		 */
		public static function js_validate_ticket_form() {

			$recaptcha = get_option( 'wpsc-recaptcha-settings' );
			if ( $recaptcha['captcha-provider'] === 'google-recaptcha' && $recaptcha['recaptcha-version'] == 2 && $recaptcha['recaptcha-site-key'] && $recaptcha['recaptcha-secret-key'] ) :
				?>
				case 'recaptcha':
					var recaptcha = jQuery("#g-recaptcha-response").val();
					if (recaptcha === "") {
						isValid = false;
					}
					break;
				<?php
				echo PHP_EOL;
			endif;

			if ( $recaptcha['captcha-provider'] === 'cloudflare-turnstile' && $recaptcha['cloudflare-site-key'] && $recaptcha['cloudflare-secret-key'] ) :
				?>
				case 'turnstile':
					var turnstile = jQuery("[name='cf-turnstile-response']").val();
					if (turnstile === "") {
						isValid = false;
						alert("<?php esc_attr_e( 'Security verification failed. Please refresh the page and try again.', 'supportcandy' ); ?>");
					}
					break;
				<?php
				echo PHP_EOL;
			endif;
		}

		/**
		 * Check whether captcha is valid or not.
		 * No need of nonce verification as we already done this where it is called from.
		 *
		 * @param string $action - action string.
		 * @return void
		 */
		public static function validate( $action = '' ) {

			$server_http_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

			$recaptcha = get_option( 'wpsc-recaptcha-settings' );
			if ( $recaptcha['captcha-provider'] === 'google-recaptcha' && $recaptcha['recaptcha-version'] == 2 && $recaptcha['recaptcha-site-key'] && $recaptcha['recaptcha-secret-key'] ) {

				$recaptcha_token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : ''; // phpcs:ignore
				if ( ! $recaptcha_token ) {
					wp_send_json_error( new WP_Error( '001', 'Recaptcha token not set' ), 400 );
				}

				$url      = 'https://www.google.com/recaptcha/api/siteverify';
				$response = wp_remote_post(
					$url,
					array(
						'method' => 'POST',
						'body'   => array(
							'secret'   => $recaptcha['recaptcha-secret-key'],
							'response' => $recaptcha_token,
						),
					)
				);

				if (
					is_wp_error( $response ) ||
					$response['response']['code'] != 200
				) {
					wp_send_json_error( new WP_Error( '002', 'Something went wrong with recaptcha!' ), 400 );
				}

				$response = json_decode( $response['body'] );
				if ( ! (
					$response->success &&
					$response->hostname == $server_http_host
				) ) {
					wp_send_json_error( new WP_Error( '003', 'Invalid recaptcha!' ), 400 );
				}
			} elseif ( $recaptcha['captcha-provider'] === 'google-recaptcha' && $recaptcha['recaptcha-version'] == 3 && $recaptcha['recaptcha-site-key'] && $recaptcha['recaptcha-secret-key'] ) {

				$recaptcha_token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : ''; // phpcs:ignore
				if ( ! $recaptcha_token ) {
					wp_send_json_error( new WP_Error( '001', 'Recaptcha token not set' ), 400 );
				}

				$url      = 'https://www.google.com/recaptcha/api/siteverify';
				$response = wp_remote_post(
					$url,
					array(
						'method' => 'POST',
						'body'   => array(
							'secret'   => $recaptcha['recaptcha-secret-key'],
							'response' => $recaptcha_token,
						),
					)
				);

				if (
					is_wp_error( $response ) ||
					$response['response']['code'] != 200
				) {
					wp_send_json_error( new WP_Error( '002', 'Something went wrong with recaptcha!' ), 400 );
				}

				$response = json_decode( $response['body'] );
				if ( ! (
					$response->success &&
					$response->action == $action &&
					$response->hostname == $server_http_host
				) ) {
					wp_send_json_error( new WP_Error( '003', 'Invalid recaptcha!' ), 400 );
				}
			} elseif ( $recaptcha['captcha-provider'] === 'cloudflare-turnstile' && $recaptcha['cloudflare-site-key'] && $recaptcha['cloudflare-secret-key'] ) {

				$server_http_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
				$turnstile_token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : ''; // phpcs:ignore
				$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
				$response = wp_remote_post(
					$url,
					array(
						'method'  => 'POST',
						'timeout' => 20,
						'body'    => array(
							'secret'   => $recaptcha['cloudflare-secret-key'],
							'response' => $turnstile_token,
							'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '', // phpcs:ignore
						),
					)
				);

				if (
					is_wp_error( $response ) ||
					wp_remote_retrieve_response_code( $response ) !== 200
				) {

					wp_send_json_error(
						new WP_Error(
							'002',
							'Something went wrong with captcha!'
						),
						400
					);
				}

				$response = json_decode(
					wp_remote_retrieve_body( $response )
				);

				if ( empty( $response->success ) ) {

					wp_send_json_error(
						new WP_Error(
							'003',
							'Invalid captcha!'
						),
						400
					);
				}
			}
		}
	}
endif;

WPSC_MS_Recaptcha::init();
