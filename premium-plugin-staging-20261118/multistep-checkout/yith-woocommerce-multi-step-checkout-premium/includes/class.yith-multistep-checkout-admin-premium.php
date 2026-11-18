<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * YITH_Multistep_Checkout_Admin_Premium Class.
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'YITH_WCMS_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Multistep_Checkout_Admin_Premium' ) ) {
	/**
	 * Class YITH_Multistep_Checkout_Admin_Premium
	 *
	 */
	class YITH_Multistep_Checkout_Admin_Premium extends YITH_Multistep_Checkout_Admin {

		/**
		 * Construct
		 *
		 * @since  1.0
		 */
		public function __construct() {
			$this->show_premium_landing = false;

			/* === Premium Options === */
			add_filter( 'yith_wcms_admin_tabs', array( $this, 'admin_tabs' ) );

			/* === Enqueue Scripts === */
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );

			/* === WooCommerce Options Customizzation === */
			add_action( 'woocommerce_admin_field_yith_wcms_media_upload', array( $this, 'option_media_upload' ) );

			parent::__construct();
		}

		/**
		 * Add premium admin tabs options
		 *
		 * @param array $free The tabs array.
		 *
		 * @return Array The tabs array
		 * @since 1.0.0
		 */
		public function admin_tabs( $free ) {
			$premium = array(
				'thankyou' => esc_html_x( 'Pages Options', 'Admin: Page title', 'yith-woocommerce-multi-step-checkout' ),
			);

			return array_merge( $free, $premium );
		}

		/**
		 * Admin enqueue scripts
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			$is_plugin_panel = ! empty( $_GET['page'] ) && $_GET['page'] === $this->get_panel_page(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_tab     = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'steps'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$script_version  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? wp_rand() : YITH_WCMS_VERSION;
			$prefix          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			$localize = array(
				'current_tab' => ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'steps', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'icons_url'   => YITH_WCMS_ASSETS_URL . 'images/icons/',
			);
			wp_register_script( 'yith-wcms-admin', YITH_WCMS_ASSETS_URL . 'js/admin' . $prefix . '.js', array( 'jquery' ), $script_version, true );
			wp_localize_script( 'yith-wcms-admin', 'yith_wcms_admin', $localize );

			if ( $is_plugin_panel ) {
				$css    = '';
				$handle = 'yith-plugin-fw-fields';

				if ( 'steps' === $current_tab || 'buttons' === $current_tab ) {
					$css .= '.yith-wcms-disabled{display:none!important} .yith-wcms-default-icon-item{margin-right: 5px; vertical-align: text-bottom;} .yith-wcms-default-icon-wrapper {text-transform: capitalize;}';
					$css .= '#yith_wcms_timeline_template-wrapper .yith-plugin-fw-select-images__item.horizontal {width: 25%; display: inline-block;}';
					$css .= '#yith_wcms_timeline_template-wrapper .yith-plugin-fw-select-images__list {display:block;}';
					$css .= '#yith_wcms_timeline_template-wrapper .yith-plugin-fw-select-images__item.vertical {display: inline-block;width: calc(25% - 10px);max-height: 250px;max-width: 180px;}';
					$css .= '@media(max-width: 768px){#yith_wcms_timeline_template-wrapper .yith-plugin-fw-select-images__item {width: auto;}}';
					wp_enqueue_script( 'yith-wcms-admin' );
				}

				/**
				 * APPLY_FILTERS: yith_wcms_add_inline_styles
				 *
				 * Filters the extra CSS rules to add to the plugin backend styles.
				 *
				 * @param string $css CSS rules.
				 *
				 * @return string
				 */
				$css = apply_filters( 'yith_wcms_add_inline_styles', $css );

				if ( ! empty( $css ) ) {
					wp_add_inline_style( $handle, $css );
				}
			}
		}

		/**
		 * Custom WooCommerce upload option
		 *
		 * @param array $value The option value array.
		 *
		 * @return void
		 * @since  1.0.0
		 */
		public function option_media_upload( $value ) {
			$image_id = get_option( $value['id'], $value['default'] );
			$args     = array(
				'image_wrapper_id'    => 'yith_wcms_image_wrapper_id_' . $value['custom_attributes']['data-step'],
				'hidden_field_id'     => 'yith_wcms_hidden_field_id_' . $value['custom_attributes']['data-step'],
				'hidden_field_name'   => 'yith_wcms_hidden_field_name_' . $value['custom_attributes']['data-step'],
				'remove_image_button' => 'yith_wcms_remove_image_button_' . $value['custom_attributes']['data-step'],
				'upload_image_button' => 'yith_wcms_upload_image_button',
			);

			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			ob_start(); ?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				</th>
				<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
					<div id="<?php echo esc_attr( $args['image_wrapper_id'] ); ?>" class="yith-wcms-icon-preview"
							style="background-color: #e2e2e2; padding: 5px; display: inline-block; margin: 0 10px 10px 0;">
						<img src="<?php echo esc_url( ! is_numeric( $image_id ) ? $value['default'] : wp_get_attachment_url( $image_id ) ); ?>"
								style="max-height: 50px; width: auto;"/>
					</div>

					<input type="hidden" id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>"
							value="<?php echo esc_attr( is_numeric( $image_id ) ? $image_id : '' ); ?>"
							data-default="<?php echo esc_attr( is_numeric( $image_id ) ? 'no' : 'yes' ); ?>"/>
					<button style="vertical-align: bottom; margin-bottom: 10px;" type="button"
							class="<?php echo esc_attr( $upload_image_button ); ?> button"
							data-step="<?php echo esc_attr( $value['custom_attributes']['data-step'] ); ?>"><?php esc_html_e( 'Upload/Add Icon', 'yith-woocommerce-multi-step-checkout' ); ?></button>
					<button style="vertical-align: bottom; margin-bottom: 10px;" type="button"
							id="<?php echo esc_attr( $remove_image_button ); ?>" class="button yith_wcms_remove_image_button"
							data-step="<?php echo esc_attr( $value['custom_attributes']['data-step'] ); ?>"
							data-default="<?php echo esc_attr( is_numeric( $image_id ) ? 'no' : 'yes' ); ?>"><?php esc_html_e( 'Restore default icon', 'yith-woocommerce-multi-step-checkout' ); ?></button>
					<span class="description" style="display: block;"><?php echo wp_kses_post( $value['desc'] ); ?></span>
				</td>
			</tr>
			<?php
			echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Action Links
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param array $links Plugin links.
		 *
		 * @return   mixed Array
		 * @use      plugin_action_links_{$plugin_file_name}
		 * @since    1.6.5
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->panel_page, true, YITH_WCMS_SLUG );

			return $links;
		}

		/**
		 * Plugin row meta.
		 *
		 * @param array    $new_row_meta_args The arguments of the new row meta.
		 * @param string[] $plugin_meta The plugin meta.
		 * @param string   $plugin_file The plugin file.
		 * @param array    $plugin_data The plugin data.
		 * @param string   $status The status.
		 * @param string   $init_file The init file.
		 *
		 * @return   Array
		 * @since    1.6.5
		 * @use      plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCMS_INIT' ) {
			$new_row_meta_args = parent::plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );

			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Get the default icons list
		 *
		 * @return string[] The icons list
		 * @since 2.0
		 */
		public function get_default_icons_list() {
			return array(
				'login'     => 'login',
				'login2'    => 'login2',
				'billing'   => 'billing',
				'billing2'  => 'billing2',
				'shipping'  => 'shipping',
				'shipping2' => 'shipping2',
				'order'     => 'order',
				'order2'    => 'order2',
				'payment'   => 'payment',
				'payment2'  => 'payment2',
			);
		}
	}
}
