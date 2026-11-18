<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * YITH_Multistep_Checkout_Premium Class.
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'YITH_WCMS_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! class_exists( 'YITH_Multistep_Checkout_Premium' ) ) {
	/**
	 * Class YITH_Multistep_Checkout_Premium
	 *
	 */
	class YITH_Multistep_Checkout_Premium extends YITH_Multistep_Checkout {

		/**
		 * The Image Sizes array
		 *
		 * @var array|mixed|void
		 */
		public $sizes = array();

		/**
		 * Constructor
		 */
		public function __construct() {
			// init image sizes array.
			$this->sizes = array(
				'yith_wcms_timeline_style1'            => array(
					'width'  => 39,
					'height' => 39,
					'crop'   => true,
				),
				'yith_wcms_timeline_style2'            => array(
					'width'  => 18,
					'height' => 18,
					'crop'   => true,
				),
				'yith_wcms_timeline_style3'            => array(
					'width'  => 25,
					'height' => 25,
					'crop'   => true,
				),
				'yith_wcms_timeline_style4_horizontal' => array(
					'width'  => 80,
					'height' => 75,
					'crop'   => true,
				),
				'yith_wcms_timeline_style4_vertical'   => array(
					'width'  => 45,
					'height' => 40,
					'crop'   => true,
				),
			);

			/* === Premium Initialization === */
			add_filter( 'yith_wcms_require_class', array( $this, 'load_premium_classes' ) );
			add_filter( 'after_setup_theme', array( $this, 'add_image_sizes' ) );

			/* === Register plugin to licence/update system === */
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );

			parent::__construct();
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
				$this->admin = new YITH_Multistep_Checkout_Admin_Premium();
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
					$this->frontend = new YITH_Multistep_Checkout_Frontend_Premium();
				}
			}
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
		 * Add premium files to Require array
		 *
		 * @param array $require The require files array.
		 *
		 * @return Array
		 * @since 1.0
		 */
		public function load_premium_classes( $require ) {
			$require['admin'][]    = 'includes/class.yith-multistep-checkout-admin-premium.php';
			$require['admin'][]    = 'includes/class.yith-multistep-checkout-privacy.php';
			$require['frontend'][] = 'includes/class.yith-multistep-checkout-frontend-premium.php';
			$require['common'][]   = 'includes/functions.yith-wcms-premium.php';
			return $require;
		}

		/**
		 * Add a body class(es)
		 *
		 * @param array $classes The classes array.
		 *
		 * @since 1.0
		 * @return array
		 */
		public function body_class( $classes ) {
			$classes   = parent::body_class( $classes );
			$classes[] = 'yith-wcms-pro';
			return $classes;
		}

		/**
		 * Add timeline image sizes
		 *
		 * @since   1.0
		 * @return  void
		 * @fire    yith_wcms_image_sizes filter
		 */
		public function add_image_sizes() {
			foreach ( $this->sizes as $name => $size ) {
				extract( $size ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
				add_image_size( $name, $width, $height, $crop );
			}
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_WCMS_PATH . 'plugin-fw/lib/yit-plugin-licence.php';
			}

			YIT_Plugin_Licence()->register( YITH_WCMS_INIT, YITH_WCMS_SECRETKEY, YITH_WCMS_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_WCMS_PATH . 'plugin-fw/lib/yit-upgrade.php';
			}

			YIT_Upgrade()->register( YITH_WCMS_SLUG, YITH_WCMS_INIT );
		}
	}
}
