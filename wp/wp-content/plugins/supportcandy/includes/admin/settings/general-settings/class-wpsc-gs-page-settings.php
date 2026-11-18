<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_GS_Page_Settings' ) ) :

	final class WPSC_GS_Page_Settings {

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// Search WP pages.
			add_action( 'wp_ajax_wpsc_search_wp_pages', array( __CLASS__, 'search_wp_pages' ) );

			// User interface.
			add_action( 'wp_ajax_wpsc_get_gs_page_settings', array( __CLASS__, 'load_settings_ui' ) );
			add_action( 'wp_ajax_wpsc_set_gs_page_settings', array( __CLASS__, 'save_settings' ) );
			add_action( 'wp_ajax_wpsc_reset_gs_page_settings', array( __CLASS__, 'reset_settings' ) );

			// One-click page creation.
			add_action( 'wp_ajax_wpsc_gs_create_page', array( __CLASS__, 'ajax_create_page' ) );

			// After page delete in WP.
			add_action( 'delete_post', array( __CLASS__, 'after_delete_post' ), 10, 2 );

			// WP_Query search by title.
			add_filter( 'posts_where', array( __CLASS__, 'search_wp_page_title' ), 10, 2 );
		}

		/**
		 * Reset settings
		 *
		 * @return void
		 */
		public static function reset() {

			$page_settings = apply_filters(
				'wpsc_gs_page_settings',
				array(
					'support-page'            => 0,
					'open-ticket-page'        => 0,
					'ticket-url-page'         => 'support-page',
					'new-ticket-page'         => 'default',
					'new-ticket-url'          => '',
					'user-login'              => 'default',
					'custom-login-url'        => '',
					'user-registration'       => 'disable',
					'custom-registration-url' => '',
					'otp-login'               => 1,
					'load-scripts'            => 'auto',
					'load-script-pages'       => array(),
				)
			);
			update_option( 'wpsc-gs-page-settings', $page_settings );
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
			$settings      = get_option( 'wpsc-gs-page-settings', array() );
			$general       = get_option( 'wpsc-gs-general', array() );
			$guest_allowed = in_array( 'guest', isset( $general['allow-create-ticket'] ) ? (array) $general['allow-create-ticket'] : array(), true );
			?>

			<form action="#" onsubmit="return false;" class="wpsc-frm-gs-ps">
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Pages', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Support page', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Page that holds the [supportcandy] shortcode, where users sign in, create tickets and view their tickets.', 'supportcandy' ); ?></p>
							<select id="wpsc-support-page" class="wpsc-select-wp-page" name="support-page">
								<?php
								if ( $settings['support-page'] ) {
									$page = get_post( $settings['support-page'] )
									?>
									<option value="<?php echo esc_attr( $page->ID ); ?>"><?php echo esc_attr( $page->post_title ); ?></option>
									<?php
								} else {
									?>
									<option value="0"></option>
									<?php
								}
								?>
							</select>
							<?php self::print_page_actions( 'support-page', $settings['support-page'] ); ?>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Ticket Lookup page (guests)', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Page that holds the [wpsc_open_ticket] shortcode, where a visitor can open an existing ticket by verifying their email with a one-time code. When set, the sign-in screen links to it.', 'supportcandy' ); ?></p>
							<select id="wpsc-ot-page" class="wpsc-select-wp-page" name="open-ticket-page">
								<?php
								if ( $settings['open-ticket-page'] ) {
									$page = get_post( $settings['open-ticket-page'] )
									?>
									<option value="<?php echo esc_attr( $page->ID ); ?>"><?php echo esc_attr( $page->post_title ); ?></option>
									<?php
								} else {
									?>
									<option value="0"></option>
									<?php
								}
								?>
							</select>
							<?php self::print_page_actions( 'open-ticket-page', $settings['open-ticket-page'] ); ?>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Ticket url page', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Page used to build the ticket link given to customers, such as the {{ticket_url}} macro in emails.', 'supportcandy' ); ?></p>
							<select id="wpsc-tu-page" name="ticket-url-page">
								<option <?php selected( $settings['ticket-url-page'], 'support-page' ); ?> value="support-page"><?php esc_attr_e( 'Support page', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['ticket-url-page'], 'open-ticket-page' ); ?> value="open-ticket-page"><?php esc_attr_e( 'Ticket Lookup page', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'New ticket page', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Where users go to create a new ticket. "Custom" sends them to the URL you enter instead of the ticket form on the support page.', 'supportcandy' ); ?></p>
							<select class="wpsc-login-url" name="new-ticket-page">
								<option <?php selected( $settings['new-ticket-page'], 'default' ); ?> value="default"><?php esc_attr_e( 'Support page', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['new-ticket-page'], 'custom' ); ?> value="custom"><?php esc_attr_e( 'Custom', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group" style="<?php echo $settings['new-ticket-page'] != 'custom' ? 'display:none' : ''; ?>">
							<input type="text" name="new-ticket-url" value="<?php echo esc_url( $settings['new-ticket-url'] ); ?>" placeholder="e.g. https://yourdomain.com/create-ticket" autocomplete="off">
						</div>
					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Sign-in & Registration', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'User login', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'How users sign in from the SupportCandy sign-in screen. "Default" shows the built-in login form, "WP Default Login" sends them to the WordPress login page and "Custom" to the URL you enter.', 'supportcandy' ); ?></p>
							<select class="wpsc-login-url" name="user-login">
								<option <?php selected( $settings['user-login'], 'default' ); ?> value="default"><?php esc_attr_e( 'Default', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['user-login'], 'wp-default' ); ?> value="wp-default"><?php esc_attr_e( 'WP Default Login', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['user-login'], 'custom' ); ?> value="custom"><?php esc_attr_e( 'Custom', 'supportcandy' ); ?></option>
							</select>
						</div>
						<div class="wpsc-input-group" style="<?php echo $settings['user-login'] != 'custom' ? 'display:none' : ''; ?>">
							<input type="text" name="custom-login-url" value="<?php echo esc_url( $settings['custom-login-url'] ); ?>" placeholder="e.g. https://yourdomain.com/login" autocomplete="off">
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'User registration', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Adds a "Register now" link to the sign-in screen. Choose the built-in form, the WordPress registration page or your own URL, or "Disable" to hide the link.', 'supportcandy' ); ?></p>
							<select class="wpsc-login-url" name="user-registration">
								<option <?php selected( $settings['user-registration'], 'disable' ); ?> value="disable"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['user-registration'], 'default' ); ?> value="default"><?php esc_attr_e( 'Default', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['user-registration'], 'wp-default' ); ?> value="wp-default"><?php esc_attr_e( 'WP Default Registration', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['user-registration'], 'custom' ); ?> value="custom"><?php esc_attr_e( 'Custom', 'supportcandy' ); ?></option>
							</select>
							<?php
							if ( ! $guest_allowed ) {
								?>
								<small class="wpsc-no-guest-access-warning" style="color:#d63638; <?php echo $settings['user-registration'] != 'disable' ? 'display:none' : ''; ?>"><?php esc_attr_e( 'Logged-out or Guest visitors cannot submit tickets. Select \'Guest\' option from \'Allow create new ticket\' in \'General Settings\' to enable guest ticket submission.', 'supportcandy' ); ?></small>
								<?php
							}
							?>
						</div>
						<div class="wpsc-input-group" style="<?php echo $settings['user-registration'] != 'custom' ? 'display:none' : ''; ?>">
							<input type="text" name="custom-registration-url" value="<?php echo esc_url( $settings['custom-registration-url'] ); ?>" placeholder="e.g. https://yourdomain.com/register" autocomplete="off">
						</div>
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'OTP Login', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Lets customers sign in with a one-time code sent to their email instead of a password. Only available when guests are allowed to create tickets.', 'supportcandy' ); ?></p>
							<select name="otp-login">
								<option <?php selected( $settings['otp-login'], 0 ); ?> value="0"><?php esc_attr_e( 'Disable', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['otp-login'], 1 ); ?> value="1"><?php esc_attr_e( 'Enable', 'supportcandy' ); ?></option>
							</select>
						</div>

					</div>
				</div>
				<div class="wpsc-setting-group">
					<h3 class="wpsc-setting-group-title"><?php echo esc_html__( 'Front-end Loading', 'supportcandy' ); ?></h3>
					<div class="wpsc-setting-group-body">
						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Load scripts', 'supportcandy' ); ?></label>
							</div>
							<p class="wpsc-input-desc"><?php esc_attr_e( 'Choose on which front-end pages SupportCandy loads its scripts and styles.', 'supportcandy' ); ?></p>
							<select class="wpsc-load-scripts" name="load-scripts">
								<option <?php selected( $settings['load-scripts'], 'auto' ); ?> value="auto"><?php esc_attr_e( 'Automatic (recommended)', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['load-scripts'], 'all-pages' ); ?> value="all-pages"><?php esc_attr_e( 'All Pages', 'supportcandy' ); ?></option>
								<option <?php selected( $settings['load-scripts'], 'custom' ); ?> value="custom"><?php esc_attr_e( 'Custom', 'supportcandy' ); ?></option>
							</select>
							<small class="wpsc-load-scripts-desc" data-mode="auto" style="<?php echo $settings['load-scripts'] != 'auto' ? 'display:none' : ''; ?>"><?php esc_attr_e( 'Automatic loads scripts only on the support page, the open ticket page and pages whose content contains a SupportCandy shortcode or block. If you use a shortcode in a header, footer, sidebar, widget or theme template, choose All Pages.', 'supportcandy' ); ?></small>
							<small class="wpsc-load-scripts-desc" data-mode="all-pages" style="<?php echo $settings['load-scripts'] != 'all-pages' ? 'display:none' : ''; ?>"><?php esc_attr_e( 'Loads scripts on every page of your site.', 'supportcandy' ); ?></small>
							<small class="wpsc-load-scripts-desc" data-mode="custom" style="<?php echo $settings['load-scripts'] != 'custom' ? 'display:none' : ''; ?>"><?php esc_attr_e( 'Loads scripts on the pages selected below and always on the configured SupportCandy pages (support, ticket lookup, new ticket and ticket URL pages).', 'supportcandy' ); ?></small>
						</div>
						<div class="wpsc-input-group" style="<?php echo $settings['load-scripts'] != 'custom' ? 'display:none' : ''; ?>">
							<select id="wpsc-load-script-pages" class="wpsc-select-wp-page" name="load-script-pages[]" multiple>
								<?php
								foreach ( $settings['load-script-pages'] as $page_id ) {
									?>
									<option selected value="<?php echo esc_attr( $page_id ); ?>"><?php echo esc_attr( get_the_title( $page_id ) ); ?></option>
									<?php
								}
								?>
							</select>
						</div>

						<div class="wpsc-input-group">
							<div class="label-container">
								<label for=""><?php esc_attr_e( 'Shortcodes', 'supportcandy' ); ?></label>
							</div>
							<small style="padding-bottom: 5px;"><?php esc_attr_e( 'Each shortcode is also available as a block (block editor) and as a widget (Elementor) in the "SupportCandy" category.', 'supportcandy' ); ?></small>
							<table class="wpsc-shortcode-list" style="width:100%;border-collapse:collapse;">
								<?php foreach ( WPSC_Blocks::get_blocks() as $block ) : ?>
									<tr style="border-bottom:1px solid #dcdcde;">
										<td style="padding:6px 8px 6px 10px;"><code>[<?php echo esc_html( $block['shortcode'] ); ?>]</code></td>
										<td style="padding:6px 8px;"><?php echo esc_html( $block['description'] ); ?></td>
										<td style="padding:6px;text-align:right;width:32px;">
											<?php WPSC_Functions::copy_button( '[' . $block['shortcode'] . ']' ); ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</table>
						</div>

					</div>
				</div>
				<?php do_action( 'wpsc_gs_page_settings' ); ?>

				<script>
					jQuery('.wpsc-login-url, .wpsc-load-scripts').change(function(){
						var urlInput = jQuery(this).closest('.wpsc-input-group').next();
						if (jQuery(this).val() === 'custom') {
							urlInput.show();
						} else {
							urlInput.hide();
						}
					});
					jQuery('.wpsc-load-scripts').change(function(){
						var mode = jQuery(this).val();
						jQuery('.wpsc-load-scripts-desc').hide().filter('[data-mode="' + mode + '"]').show();
					});
					jQuery('.wpsc-create-page').click(function(){
						var btn = jQuery(this);
						var type = btn.data('type');
						var label = btn.text();
						btn.prop('disabled', true).text(supportcandy.translations.please_wait);
						jQuery.post(supportcandy.ajax_url, {
							action: 'wpsc_gs_create_page',
							type: type,
							_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_gs_create_page' ) ); ?>'
						}, function(res){
							if (res.success) {
								var select = jQuery('select[name="' + type + '"]');
								select.empty().append(new Option(res.data.title, res.data.page_id, true, true)).trigger('change');
								btn.hide();
							} else {
								alert(res.data);
							}
						}).always(function(){
							btn.prop('disabled', false).text(label);
						});
					});
					jQuery('select[name="support-page"], select[name="open-ticket-page"]').change(function(){
						var pageId = parseInt(jQuery(this).val(), 10) || 0;
						var actions = jQuery('.wpsc-page-actions[data-type="' + this.name + '"]');
						actions.find('.wpsc-create-page').toggle(!pageId);
						actions.find('.wpsc-view-page').toggle(!!pageId).attr('href', '<?php echo esc_js( add_query_arg( 'page_id', '', home_url( '/' ) ) ); ?>' + pageId);
					});
					jQuery('select[name="user-registration"]').change(function(){
						jQuery('.wpsc-no-guest-access-warning').toggle(jQuery(this).val() === 'disable');
					});
					jQuery('.wpsc-select-wp-page').selectWoo({
						ajax: {
							url: supportcandy.ajax_url,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									q: params.term, // search term
									page: params.page,
									action: 'wpsc_search_wp_pages',
									_ajax_nonce: '<?php echo esc_attr( wp_create_nonce( 'wpsc_search_wp_pages' ) ); ?>'
								};
							},
							processResults: function (data, params) {
								var terms = [];
								if ( data ) {
									jQuery.each( data, function( id, text ) {
										terms.push( { id: text.id, text: text.title } );
									});
								}
								return {
									results: terms
								};
							},
							cache: true
						},
						escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
						minimumInputLength: 1,
						allowClear: true,
						placeholder: ""
					});
				</script>
				<input type="hidden" name="action" value="wpsc_set_gs_page_settings">
				<input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wpsc_set_gs_page_settings' ) ); ?>">

			</form>
			<div class="setting-footer-actions">
				<button 
					class="wpsc-button normal primary margin-right"
					onclick="wpsc_set_gs_page_settings(this);">
					<?php esc_attr_e( 'Submit', 'supportcandy' ); ?></button>
				<button 
					class="wpsc-button normal secondary"
					onclick="wpsc_reset_gs_page_settings(this, '<?php echo esc_attr( wp_create_nonce( 'wpsc_reset_gs_page_settings' ) ); ?>');">
					<?php esc_attr_e( 'Reset default', 'supportcandy' ); ?></button>
			</div>
			<?php
			wp_die();
		}

		/**
		 * Print "Create page" button (when not set) and "View page" link (when set) below a page setting
		 *
		 * @param string  $type - 'support-page' or 'open-ticket-page'.
		 * @param integer $page_id - currently selected page id.
		 * @return void
		 */
		private static function print_page_actions( $type, $page_id ) {

			$page_id = intval( $page_id );
			?>
			<div class="wpsc-page-actions" data-type="<?php echo esc_attr( $type ); ?>">
				<button type="button" class="wpsc-button small secondary wpsc-create-page" data-type="<?php echo esc_attr( $type ); ?>" style="<?php echo $page_id ? 'display:none;' : ''; ?>"><?php esc_attr_e( 'Create page', 'supportcandy' ); ?></button>
				<a class="wpsc-view-page" href="<?php echo $page_id ? esc_url( get_permalink( $page_id ) ) : '#'; ?>" target="_blank" style="<?php echo ! $page_id ? 'display:none;' : ''; ?>"><?php esc_attr_e( 'View page', 'supportcandy' ); ?> &#8599;</a>
			</div>
			<?php
		}

		/**
		 * AJAX: create the default support / ticket lookup page and select it.
		 *
		 * @return void
		 */
		public static function ajax_create_page() {

			if ( check_ajax_referer( 'wpsc_gs_create_page', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}
			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
			if ( ! in_array( $type, array( 'support-page', 'open-ticket-page' ), true ) ) {
				wp_send_json_error( __( 'Invalid page type!', 'supportcandy' ), 400 );
			}

			$page_id = self::create_default_page( $type );
			if ( ! $page_id ) {
				wp_send_json_error( __( 'Something went wrong!', 'supportcandy' ), 500 );
			}

			wp_send_json_success(
				array(
					'page_id' => $page_id,
					'title'   => get_the_title( $page_id ),
				)
			);
		}

		/**
		 * Save settings
		 *
		 * @return void
		 */
		public static function save_settings() {

			if ( check_ajax_referer( 'wpsc_set_gs_page_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$new_ticket_url  = isset( $_POST['new-ticket-url'] ) && filter_var( wp_unslash( $_POST['new-ticket-url'] ), FILTER_VALIDATE_URL ) ? sanitize_text_field( wp_unslash( $_POST['new-ticket-url'] ) ) : '';
			$new_ticket_page = isset( $_POST['new-ticket-page'] ) ? sanitize_text_field( wp_unslash( $_POST['new-ticket-page'] ) ) : 'default';
			if ( $new_ticket_page == 'custom' && ! $new_ticket_url ) {
				$new_ticket_page = 'default';
			}

			$custom_login_url = isset( $_POST['custom-login-url'] ) && filter_var( wp_unslash( $_POST['custom-login-url'] ), FILTER_VALIDATE_URL ) ? sanitize_text_field( wp_unslash( $_POST['custom-login-url'] ) ) : '';
			$user_login       = isset( $_POST['user-login'] ) ? sanitize_text_field( wp_unslash( $_POST['user-login'] ) ) : 'default';
			if ( $user_login == 'custom' && ! $custom_login_url ) {
				$user_login = 'default';
			}

			$custom_registration_url = isset( $_POST['custom-registration-url'] ) && filter_var( wp_unslash( $_POST['custom-registration-url'] ), FILTER_VALIDATE_URL ) ? sanitize_text_field( wp_unslash( $_POST['custom-registration-url'] ) ) : '';
			$user_registration       = isset( $_POST['user-registration'] ) ? sanitize_text_field( wp_unslash( $_POST['user-registration'] ) ) : 'disable';
			if ( $user_registration == 'custom' && ! $custom_registration_url ) {
				$user_registration = 'disable';
			}

			$load_scripts = isset( $_POST['load-scripts'] ) ? sanitize_text_field( wp_unslash( $_POST['load-scripts'] ) ) : 'all-pages';
			if ( ! in_array( $load_scripts, array( 'all-pages', 'auto', 'custom' ), true ) ) {
				$load_scripts = 'all-pages';
			}

			$page_settings = apply_filters(
				'wpsc_set_gs_page_settings',
				array(
					'support-page'            => isset( $_POST['support-page'] ) ? intval( $_POST['support-page'] ) : 0,
					'open-ticket-page'        => isset( $_POST['open-ticket-page'] ) ? intval( $_POST['open-ticket-page'] ) : 0,
					'ticket-url-page'         => isset( $_POST['ticket-url-page'] ) ? sanitize_text_field( wp_unslash( $_POST['ticket-url-page'] ) ) : 'support-page',
					'new-ticket-page'         => $new_ticket_page,
					'new-ticket-url'          => $new_ticket_url,
					'user-login'              => $user_login,
					'custom-login-url'        => $custom_login_url,
					'user-registration'       => $user_registration,
					'custom-registration-url' => $custom_registration_url,
					'otp-login'               => isset( $_POST['otp-login'] ) ? intval( $_POST['otp-login'] ) : 0,
					'load-scripts'            => $load_scripts,
					'load-script-pages'       => isset( $_POST['load-script-pages'] ) ? array_filter( array_map( 'sanitize_text_field', wp_unslash( $_POST['load-script-pages'] ) ) ) : array(),
				)
			);
			update_option( 'wpsc-gs-page-settings', $page_settings );
			wp_die();
		}

		/**
		 * Reset settings to default
		 *
		 * @return void
		 */
		public static function reset_settings() {

			if ( check_ajax_referer( 'wpsc_reset_gs_page_settings', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}
			self::reset();
			wp_die();
		}

		/**
		 * Search WP pages
		 */
		public static function search_wp_pages() {

			if ( check_ajax_referer( 'wpsc_search_wp_pages', '_ajax_nonce', false ) != 1 ) {
				wp_send_json_error( 'Unauthorized request!', 401 );
			}

			if ( ! WPSC_Functions::is_site_admin() ) {
				wp_send_json_error( __( 'Unauthorized access!', 'supportcandy' ), 401 );
			}

			$term = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

			$args = array(
				'post_type'         => 'page',
				'post_status'       => 'publish',
				'orderby'           => array( 'title' => 'ASC' ),
				'search_wpsc_title' => $term,
			);
			$query = new WP_Query( $args );

			$response = array();
			foreach ( $query->posts as $page ) {
				$response[] = array(
					'id'    => $page->ID,
					'title' => $page->post_title,
				);
			}
			wp_send_json( $response );
		}

		/**
		 * Remove support page if already set when post is deleted
		 *
		 * @param integer $postid - post id.
		 * @param WP_Post $post - post object.
		 * @return void
		 */
		public static function after_delete_post( $postid, $post ) {

			$page_settings = get_option( 'wpsc-gs-page-settings' );

			if ( $page_settings['support-page'] == $postid ) {
				$page_settings['support-page'] = 0;
			}

			if ( $page_settings['open-ticket-page'] == $postid ) {
				$page_settings['open-ticket-page'] = 0;
			}

			update_option( 'wpsc-gs-page-settings', $page_settings );
		}

		/**
		 * Search wp posts by title.
		 *
		 * @param array $where - where array in wp_query.
		 * @param array $wp_query - wp query array.
		 * @return array
		 */
		public static function search_wp_page_title( $where, $wp_query ) {
			global $wpdb;
			$search_term = $wp_query->get( 'search_wpsc_title' );
			if ( $search_term ) {
				$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $search_term ) ) . '%\'';
			}
			return $where;
		}

		/**
		 * Create (or reuse) the default WP page for the given page-settings key and point
		 * that setting at it. Used by the onboarding wizard's one-click "Create page" actions.
		 * Does not create a duplicate page if the setting already points to a valid,
		 * non-trashed page - mirrors the support page auto-created on first install.
		 *
		 * @param string $type - 'support-page' or 'open-ticket-page'.
		 * @return integer - resulting WP page ID, or 0 on failure.
		 */
		public static function create_default_page( $type ) {

			if ( ! in_array( $type, array( 'support-page', 'open-ticket-page' ), true ) ) {
				return 0;
			}

			$settings = get_option( 'wpsc-gs-page-settings', array() );
			$existing = isset( $settings[ $type ] ) ? intval( $settings[ $type ] ) : 0;

			// Already points to a live page - do not create a duplicate.
			if ( $existing && get_post_status( $existing ) && get_post_status( $existing ) !== 'trash' ) {
				return $existing;
			}

			$titles = array(
				'support-page'     => esc_html__( 'Support', 'supportcandy' ),
				'open-ticket-page' => esc_html__( 'Ticket Lookup', 'supportcandy' ),
			);

			// [supportcandy] powers the full customer interface; [wpsc_open_ticket] is the
			// dedicated shortcode for a standalone page where guests look up an existing ticket.
			$content = array(
				'support-page'     => '[supportcandy]',
				'open-ticket-page' => '[wpsc_open_ticket]',
			);

			$page_id = wp_insert_post(
				array(
					'post_title'   => $titles[ $type ],
					'post_content' => $content[ $type ],
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);

			if ( is_wp_error( $page_id ) || ! $page_id ) {
				return 0;
			}

			$settings[ $type ] = $page_id;
			update_option( 'wpsc-gs-page-settings', $settings );

			return $page_id;
		}
	}
endif;

WPSC_GS_Page_Settings::init();
