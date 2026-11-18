<?php
/**
 * Plugin Name: YITH WooCommerce Tab Manager Premium - نوین وردپرس
 * Plugin URI: https://wpnovin.com/product/yith-woocommerce-tab-manager-wordpress-plugin-wordpress-plugin/
 * Description: <code><strong>#378775</strong></code> افزونه افزودن تب سفارشی در ووکامرس
 * Version: 2.21.0
 * Author: w w w . W P N O V I N . c o m
 * Author URI: https://wpnovin.com/product/yith-woocommerce-tab-manager-wordpress-plugin-wordpress-plugin/
 * Text Domain: yith-woocommerce-tab-manager
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'yith_ywtm_install_woocommerce_admin_notice' ) ) {
	/**
	 * Show notice if WooCommerce isn't enabled
	 *
	 * @author YITH <plugins@yithemes.com>
	 * @since 1.0.0
	 */
	function yith_ywtm_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'YITH WooCommerce Tab Manager is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-tab-manager' ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yith_deactivate_plugins' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yith_deactivate_plugins( 'YWTM_FREE_INIT', plugin_basename( __FILE__ ) );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

if ( ! defined( 'YWTM_VERSION' ) ) {
	define( 'YWTM_VERSION', '2.21.0' );
}

if ( ! defined( 'YWTM_DB_VERSION' ) ) {
	define( 'YWTM_DB_VERSION', '2.0.2' );
}

if ( ! defined( 'YWTM_PREMIUM' ) ) {
	define( 'YWTM_PREMIUM', '1' );
}

if ( ! defined( 'YWTM_INIT' ) ) {
	define( 'YWTM_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YWTM_FILE' ) ) {
	define( 'YWTM_FILE', __FILE__ );
}

if ( ! defined( 'YWTM_DIR' ) ) {
	define( 'YWTM_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YWTM_URL' ) ) {
	define( 'YWTM_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YWTM_ASSETS_URL' ) ) {
	define( 'YWTM_ASSETS_URL', YWTM_URL . 'assets/' );
}

if ( ! defined( 'YWTM_ASSETS_PATH' ) ) {
	define( 'YWTM_ASSETS_PATH', YWTM_DIR . 'assets/' );
}

if ( ! defined( 'YWTM_TEMPLATE_PATH' ) ) {
	define( 'YWTM_TEMPLATE_PATH', YWTM_DIR . 'templates/' );
}

if ( ! defined( 'YWTM_INC' ) ) {
	define( 'YWTM_INC', YWTM_DIR . 'includes/' );
}

if ( ! defined( 'YWTM_SLUG' ) ) {
	define( 'YWTM_SLUG', 'yith-woocommerce-tab-manager' );
}

if ( ! defined( 'YWTM_SECRET_KEY' ) ) {
	define( 'YWTM_SECRET_KEY', '' );
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}

register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( function_exists( 'yith_deactive_jetpack_module' ) ) {
	global $yith_jetpack_1;
	yith_deactive_jetpack_module( $yith_jetpack_1, 'YWTM_PREMIUM', plugin_basename( __FILE__ ) );
}

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

if ( ! function_exists( 'YITH_Tab_Manager_Premium_Init' ) ) {

	/* Load YWCM text domain */
	load_plugin_textdomain( 'yith-woocommerce-tab-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**
	 * Unique access to instance of YITH_Tab_Manager class
	 *
	 * @since 1.0.5
	 */
	function YITH_Tab_Manager_Premium_Init() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		// Load required classes and functions.
		require_once YWTM_INC . 'functions.yith-tab-manager-update.php';
		require_once YWTM_INC . 'functions.yith-tab-manager.php';
		require_once YWTM_INC . 'functions.yith-tab-manager-premium.php';
		require_once YWTM_INC . 'class-yith-tab-manager-autoloader.php';
		require_once YWTM_INC . 'class-yith-woocommerce-tab-manager.php';

		global $YIT_Tab_Manager; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$YIT_Tab_Manager = yith_tab_manager(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			YITH_Tab_Manager_WPML_Integration::get_instance();
		}
	}
}

add_action( 'yith_wc_tabmanager_premium_init', 'YITH_Tab_Manager_Premium_Init' );

if ( ! function_exists( 'yith_tab_manager_premium_install' ) ) {
	/**
	 * Install tab manager
	 *
	 * @since 1.0.5
	 */
	function yith_tab_manager_premium_install() {

		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'yith_ywtm_install_woocommerce_admin_notice' );
		} else {
			add_action( 'before_woocommerce_init', 'yith_tab_manager_add_support_hpos_system' );
			do_action( 'yith_wc_tabmanager_premium_init' );
		}
	}
}

add_action( 'plugins_loaded', 'yith_tab_manager_premium_install', 11 );

if ( ! function_exists( 'yith_tab_manager_add_support_hpos_system' ) ) {
	/**
	 * Declare support for HPOS
	 *
	 * @return void
	 */
	function yith_tab_manager_add_support_hpos_system() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {

			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YWTM_INIT );
		}
	}
}