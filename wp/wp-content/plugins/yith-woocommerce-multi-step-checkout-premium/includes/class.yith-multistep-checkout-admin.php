<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * YITH_Multistep_Checkout_Admin Class.
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'YITH_WCMS_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Multistep_Checkout_Admin' ) ) {
	/**
	 * Class YITH_Multistep_Checkout_Admin
	 *
	 */
	class YITH_Multistep_Checkout_Admin {

		/**
		 * Plugin Panel.
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $panel = null;


		/**
		 * Panel page
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wcms_panel';

		/**
		 * Show the premium landing page
		 *
		 * @var bool
		 */
		public $show_premium_landing = true;

		/**
		 * Official plugin documentation
		 *
		 * @var string
		 */
		protected $official_documentation = 'https://docs.yithemes.com/yith-woocommerce-multi-step-checkout/';

		/**
		 * Official plugin landing page
		 *
		 * @var string
		 */
		protected $premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-multi-step-checkout/';

		/**
		 * Official plugin landing page
		 *
		 * @var string
		 */
		protected $premium_live = 'https://plugins.yithemes.com/yith-woocommerce-multi-step-checkout/';

		/**
		 * Official plugin support page
		 *
		 * @var string
		 */
		protected $support = 'https://wordpress.org/support/plugin/yith-woocommerce-multi-step-checkout';

		/**
		 * Construct
		 *
		 * @since 1.0
		 */
		public function __construct() {
			/* === Register Panel Settings === */
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			/* === Premium Tab === */
			add_action( 'yith_wcms_premium_tab', array( $this, 'show_premium_landing' ) );

			/* === Show Plugin Information === */
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCMS_PATH . '/' . basename( YITH_WCMS_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->panel ) ) {
				return;
			}

			$page_title = 'YITH WooCommerce Multi-step Checkout';
			$menu_title = 'Multi-step Checkout';

			/**
			 * APPLY_FILTERS: yith_wcms_admin_tabs
			 *
			 * Filters the available tabs in the plugin panel.
			 *
			 * @param array $tabs Admin tabs.
			 *
			 * @return array
			 */
			$admin_tabs = apply_filters(
				'yith_wcms_admin_tabs',
				array(
					'steps'   => esc_html_x( 'Steps options', 'Admin: Page title', 'yith-woocommerce-multi-step-checkout' ),
					'buttons' => esc_html_x( 'Buttons options', 'Admin: Page title', 'yith-woocommerce-multi-step-checkout' ),
				)
			);

			if ( $this->show_premium_landing ) {
				$admin_tabs['premium'] = __( 'Premium Version', 'yith-woocommerce-multi-step-checkout' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => $page_title,
				'menu_title'       => $menu_title,
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_WCMS_OPTIONS_PATH,
				'class'            => yith_set_wrapper_class(),
                'plugin_slug'      => YITH_WCMS_SLUG,
                'is_premium'       => defined( YITH_WCMS_PREMIUM ),
			);

			/* === Fixed: not updated theme/old plugin framework  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Show the premium landing
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function show_premium_landing() {
			if ( file_exists( YITH_WCMS_TEMPLATE_PATH . 'premium.php' ) && $this->show_premium_landing ) {
				require_once YITH_WCMS_TEMPLATE_PATH . 'premium.php';
			}
		}

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @return  string The premium landing link
		 */
		public function get_premium_landing_uri() {
			return $this->premium_landing;
		}

		/**
		 * Get the panle page id
		 *
		 * @since   1.2.1
		 * @return  string The premium landing link
		 */
		public function get_panel_page() {
			return $this->panel_page;
		}

		/**
		 * Action Links
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param array $links Plugin links.
		 *
		 * @return   mixed Array
		 * @since    1.6.5
		 * @use      plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->panel_page, false );
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
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCMS_FREE_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_WCMS_SLUG;
			}

			return $new_row_meta_args;
		}
	}
}
