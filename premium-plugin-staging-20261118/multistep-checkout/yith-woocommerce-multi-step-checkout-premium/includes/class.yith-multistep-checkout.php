<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * YITH_Multistep_Checkout Class.
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'YITH_WCMS_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Multistep_Checkout' ) ) {
	/**
	 * Class YITH_Multistep_Checkout
	 *
	 */
	class YITH_Multistep_Checkout {
		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0
		 */
		public $version = YITH_WCMS_VERSION;

		/**
		 * Main Instance
		 *
		 * @var YITH_Multistep_Checkout
		 * @since 1.0
		 * @access protected
		 */
		protected static $instance = null;

		/**
		 * Main Admin Instance
		 *
		 * @var YITH_Multistep_Checkout_Admin
		 * @since 1.0
		 */
		public $admin = null;

		/**
		 * Main Frontpage Instance
		 *
		 * @var YITH_Multistep_Checkout_Frontend
		 * @since 1.0
		 */
		public $frontend = null;

		/**
		 * Construct
		 *
		 * @since 1.0
		 */
		public function __construct() {

			/* === Require Main Files === */
			/**
			 * APPLY_FILTERS: yith_wcms_require_class
			 *
			 * Filters the required files to be loaded in the plugin.
			 *
			 * @param array $files Required files.
			 *
			 * @return array
			 */
			$require = apply_filters(
				'yith_wcms_require_class',
				array(
					'common'   => array(
						'includes/functions.yith-wcms.php',
					),
					'admin'    => array(
						'includes/class.yith-multistep-checkout-admin.php',
						'includes/functions.yith-update.php',
					),
					'frontend' => array(
						'includes/class.yith-multistep-checkout-frontend.php',
					),
				)
			);

			$this->require( $require );

            add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features_support' ) );

            add_filter( 'body_class', array( $this, 'body_class' ) );

			/* === Support to Avada Theme === */
			add_filter( 'avada_setting_get_woocommerce_one_page_checkout', '__return_zero' );

			/* == Plugins Init === */
			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Multistep_Checkout Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Add the main classes file
		 *
		 * Include the admin and frontend classes
		 *
		 * @param array $main_classes The require classes file path.
		 *
		 * @since  1.0
		 *
		 * @return void
		 * @access protected
		 */
		protected function require( $main_classes ) {
			foreach ( $main_classes as $section => $classes ) {
				foreach ( $classes as $class ) {
					if ( 'common' === $section || ( 'frontend' === $section && ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) || ( 'admin' === $section && is_admin() ) && file_exists( YITH_WCMS_PATH . $class ) ) {
						require_once YITH_WCMS_PATH . $class;
					}
				}
			}
		}

        /**
         * Declare support for WooCommerce features.
         *
         * @since 2.21.0
         */
        public function declare_wc_features_support() {
            if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
                $init = defined( 'YITH_WCMS_INIT' ) ? YITH_WCMS_INIT : false;
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $init, true );
            }
        }

		/**
		 * Class Initializzation
		 *
		 * Instance the admin or frontend classes
		 *
		 * @since  1.0
		 * @return void
		 * @access protected
		 */
		public function init() {

			if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['context'] ) && 'frontend' === $_REQUEST['context'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->admin = new YITH_Multistep_Checkout_Admin();
			} else {
				/**
				 * APPLY_FILTERS: yith_wcms_plugin_enabled_on_front
				 *
				 * Filters whether the plugin is enabled on the frontend.
				 *
				 * @param bool $is_plugin_enabled_on_front Whether the plugin is enabled on the frontend or not.
				 *
				 * @return bool
				 */
				if ( apply_filters( 'yith_wcms_plugin_enabled_on_front', true ) ) {
					$this->frontend = new YITH_Multistep_Checkout_Frontend();
				}
			}
		}

		/**
		 * Add a body class(es)
		 *
		 * @param array $classes The classes array.
		 *
		 * @since 1.3.0
		 * @return array
		 */
		public function body_class( $classes ) {
			$classes[] = 'yith-wcms';
			$classes[] = defined( 'YITH_WCMS_FREE_INIT' ) ? 'yith-wcms-free' : 'yith-wcms-pro';
			$classes[] = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder', 'yes' ) ? 'show_checkout_login_reminder' : 'hide_checkout_login_reminder';
			return $classes;
		}
	}
}
