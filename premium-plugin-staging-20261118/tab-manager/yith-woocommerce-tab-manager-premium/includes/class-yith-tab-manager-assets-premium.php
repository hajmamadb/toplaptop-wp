<?php
/**
 * Class that manage the assets
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Assets_Premium' ) ) {
	/**
	 * This class register and enqueue the premium scripts
	 */
	class YITH_Tab_Manager_Assets_Premium extends YITH_Tab_Manager_Assets {


		/**
		 * The construct
		 */
		protected function __construct() {
			parent::__construct();
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
		}

		/**
		 * Register the admin assets
		 *
		 * @return void
		 */
		public function enqueue_admin_scripts() {

			parent::enqueue_admin_scripts();

			wp_register_style( 'ywtm-admin-product', YWTM_ASSETS_URL . 'css/admin-product.css', array(), YWTM_VERSION );
			wp_register_script( 'ywtm-admin-product', YWTM_ASSETS_URL . 'js/build/' . yit_load_js_file( 'admin-product.js' ), array( 'jquery' ), YWTM_VERSION, true );
			$current_screen = get_current_screen();
			if ( $current_screen && 'product' === $current_screen->post_type ) {
				wp_enqueue_editor();
				wp_enqueue_script( 'yith-plugin-fw-fields' );
				wp_enqueue_style( 'yith-plugin-fw-fields' );
				wp_enqueue_style( 'ywtm-admin' );
				wp_enqueue_script( 'ywtm-admin' );
				if ( ! wp_script_is( 'selectWoo' ) ) {
					wp_enqueue_script( 'selectWoo' );
					wp_enqueue_script( 'wc-enhanced-select' );
				}
				if ( ! wp_style_is( 'font-awesome' ) ) {
					wp_enqueue_style( 'font-awesome' );
				}
				wp_enqueue_style( 'ywtm-admin-product' );
				wp_enqueue_script( 'ywtm-admin-product' );
			}
		}

		/**
		 * Register and enqueue scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			wp_register_style( 'ywtm-frontend', YWTM_ASSETS_URL . 'css/frontend.css', array(), YWTM_VERSION );
			wp_add_inline_style( 'ywtm-frontend', yith_tab_manager_generate_css_var() );
			$extra_css = get_option( 'ywtm_custom_style', '' );
			wp_add_inline_style( 'ywtm-frontend', $extra_css );
			$gmap_key      = get_option( 'ywtm_google_api_key', '' );
			$gmaps_api_uri = '//maps.google.com/maps/api/js?language=en';
			if ( ! empty( $gmap_key ) ) {
				$gmaps_api_uri .= "&key={$gmap_key}";
			}
			wp_register_script( 'gmaps-api', $gmaps_api_uri, array( 'jquery' ), 'weekly', true );
			wp_register_script(
				'gmap3',
				YWTM_ASSETS_URL . 'js/lib/gmap/gmap3.min.js',
				array(
					'jquery',
					'gmaps-api',
				),
				'6.0.0',
				true
			);
			wp_register_script( 'ywtm-frontend', YWTM_ASSETS_URL . 'js/build/' . yit_load_js_file( 'frontend.js' ), array( 'jquery' ), YWTM_VERSION, true );
			$this->localize_scripts( 'ywtm-frontend' );
			if ( is_product() ) {
				wp_register_style( 'yith-plugin-ui', YIT_CORE_PLUGIN_URL . '/assets/css/yith-plugin-ui.css', array( 'yith-plugin-fw-icon-font' ), '1.0.0' );
				wp_enqueue_style( 'ywtm-frontend' );

				if ( ! wp_style_is( 'font-awesome' ) ) {
					wp_enqueue_style( 'font-awesome' );
				}

				$yith_tabs = ywtm_get_tabs(
					array(
						'active' => 'yes',
						'origin' => 'plugin',
						'limit'  => -1,
						'layout' => 'map',
					)
				);

				$product = wc_get_product( get_queried_object_id() );

				if ( ! $product ) {
					return;
				}
				$load_map = false;

				foreach ( $yith_tabs as $tab ) {
					$load_map = $tab->can_show( $product ) && 'map' === $tab->get_layout();
					if ( $load_map ) {
						break;
					}
				}

				if ( $load_map ) {
					wp_enqueue_script( 'gmap3' );
				}
				wp_enqueue_script( 'ywtm-frontend' );
			}
		}

		/**
		 * Localize the scripts
		 *
		 * @param string $handle The handle.
		 *
		 * @return void
		 */
		protected function localize_scripts( $handle ) {
			$args = array(
				'ywtm-admin'    => array(
					'ajax_url'      => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'actions'       => array(
						'toggle_show_tab' => 'ywtm_toggle_show_tab',
						'sort_tabs'       => 'ywtm_sort_tabs',
					),
					'nonces'        => array(
						'toggle_show_tab' => wp_create_nonce( 'toggle-show-tab' ),
						'sort_tabs'       => wp_create_nonce( 'sort-tabs' ),
					),
					'message_alert' => array(
						'title'         => __( 'Are you sure?', 'yith-woocommerce-tab-manager' ),
						'desc'          => __( 'Do you want to go back to the plugin dashboard? The changes made may not be saved.', 'yith-woocommerce-tab-manager' ),
						'confirmButton' => __( 'Yes, proceed without saving', 'yith-woocommerce-tab-manager' ),
					),
					'fields_labels' => array(
						'_ywtm_tab_description' => array(
							'video'    => __( 'Set additional content to show above the video(s).', 'yith-woocommerce-tab-manager' ),
							'gallery'  => __( 'Set additional content to show above the image(s).', 'yith-woocommerce-tab-manager' ),
							'faq'      => __( 'Set additional content to show above the FAQs list.', 'yith-woocommerce-tab-manager' ),
							'download' => __( 'Set additional content to show above the downloadable file(s).', 'yith-woocommerce-tab-manager' ),
							'map'      => __( 'Set additional content to show above the map.', 'yith-woocommerce-tab-manager' ),
							'contact'  => __( 'Set additional content to show above the form.', 'yith-woocommerce-tab-manager' ),
						),
					),
					'media_gallery' => array(
						'button'       => __( 'Select an image', 'yith-woocommerce-tab-manager' ),
						'noImageError' => __( 'No image selected', 'yith-woocommerce-tab-manager' ),
					),
				),
				'ywtm-frontend' => array(
					'ajax_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'actions'  => array(
						'send_form' => 'ywtm_send_form',

					),
					'nonces'   => array(
						'send_form' => wp_create_nonce( 'send-form' ),
					),
					'message'  => array(
						'required' => __( 'This field is required', 'yith-woocommerce-tab-manager' ),
					),
				),
			);

			if ( isset( $args[ $handle ] ) ) {
				$object_name = str_replace( '-', '_', $handle );
				wp_localize_script( $handle, "{$object_name}_args", $args[ $handle ] );
			}
		}
	}
}
