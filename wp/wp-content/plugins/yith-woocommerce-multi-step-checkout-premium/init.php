<?php
/**
 * Plugin Name: YITH WooCommerce Multi-step Checkout Premium - نوین وردپرس
 * Plugin URI: https://wpnovin.com/product/woocommerce-multi-step-checkout-wordpress-plugin/
 * Description: <code><strong>#378756</strong></code> افزونه چند مرحله ای کردن صفحه پرداخت ووکامرس
 * Author: w w w . W P N O V I N . c o m
 * Text Domain: yith-woocommerce-multi-step-checkout
 * Version: 2.58.0
 * Author URI: https://wpnovin.com/product/woocommerce-multi-step-checkout-wordpress-plugin/
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'install_premium_woocommerce_admin_notice' ) ) {
	/**
	 * Print an admin notice if woocommerce is deactivated
	 *
	 * @return void
	 * @use admin_notices hooks
	 * @since 1.0
	 */
	function install_premium_woocommerce_admin_notice() { ?>
		<div class="error">
			<p><?php printf( '%s %s', 'YITH WooCommerce Multi-step Checkout Premium', esc_html_x( 'is enabled but not effective. It requires WooCommerce in order to work.', 'Alert Message: WooCommerce requires', 'yith-woocommerce-multi-step-checkout' ) ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'yit_deactive_free_version' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}

function_exists('yith_deactivate_plugins') ?  yith_deactivate_plugins( 'YITH_WCMS_FREE_INIT', plugin_basename( __FILE__ ) ) : yit_deactive_free_version('YITH_WCMS_FREE_INIT', plugin_basename( __FILE__ ) ) ;

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

/* === DEFINE === */
! defined( 'YITH_WCMS_VERSION' ) && define( 'YITH_WCMS_VERSION', '2.58.0' );
! defined( 'YITH_WCMS_DB_VERSION' ) && define( 'YITH_WCMS_DB_VERSION', '2.0.1' );
! defined( 'YITH_WCMS_INIT' ) && define( 'YITH_WCMS_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_WCMS_SLUG' ) && define( 'YITH_WCMS_SLUG', 'yith-woocommerce-multi-step-checkout' );
! defined( 'YITH_WCMS_SECRETKEY' ) && define( 'YITH_WCMS_SECRETKEY', 'Ynba8JwCe4NyjjHe8ecn' );
! defined( 'YITH_WCMS_FILE' ) && define( 'YITH_WCMS_FILE', __FILE__ );
! defined( 'YITH_WCMS_PATH' ) && define( 'YITH_WCMS_PATH', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_WCMS_URL' ) && define( 'YITH_WCMS_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_WCMS_ASSETS_URL' ) && define( 'YITH_WCMS_ASSETS_URL', YITH_WCMS_URL . 'assets/' );
! defined( 'YITH_WCMS_ASSETS_PATH' ) && define( 'YITH_WCMS_ASSETS_PATH', YITH_WCMS_PATH . 'assets/' );
! defined( 'YITH_WCMS_TEMPLATE_PATH' ) && define( 'YITH_WCMS_TEMPLATE_PATH', YITH_WCMS_PATH . 'templates/' );
! defined( 'YITH_WCMS_WC_TEMPLATE_PATH' ) && define( 'YITH_WCMS_WC_TEMPLATE_PATH', YITH_WCMS_PATH . 'templates/woocommerce/' );
! defined( 'YITH_WCMS_OPTIONS_PATH' ) && define( 'YITH_WCMS_OPTIONS_PATH', YITH_WCMS_PATH . 'plugin-options' );
! defined( 'YITH_WCMS_PREMIUM' ) && define( 'YITH_WCMS_PREMIUM', '1' );

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

/* Load YWCM text domain */
if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
	yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-multi-step-checkout', basename( dirname( __FILE__ ) ) . '/languages' );
}

if ( ! function_exists( 'YITH_Multistep_Checkout' ) ) {
	/**
	 * Unique access to instance of YITH_Vendors class
	 *
	 * @return YITH_Multistep_Checkout|YITH_Multistep_Checkout_Premium
	 * @since 1.0.0
	 */
	function YITH_Multistep_Checkout() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		// Load required classes and functions.
		require_once YITH_WCMS_PATH . 'includes/class.yith-multistep-checkout.php';
		if ( defined( 'YITH_WCMS_PREMIUM' ) && file_exists( YITH_WCMS_PATH . 'includes/class.yith-multistep-checkout-premium.php' ) ) {
			require_once YITH_WCMS_PATH . 'includes/class.yith-multistep-checkout-premium.php';

			return YITH_Multistep_Checkout_Premium::instance();
		}

		return YITH_Multistep_Checkout::instance();
	}
}

if ( ! function_exists( 'yith_wcms_install' ) ) {
	/**
	 * Check WC installation
	 */
	function yith_wcms_install() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'install_premium_woocommerce_admin_notice' );
		} else {
			/**
			 * Instance main plugin class
			 */
			YITH_Multistep_Checkout();
		}
	}
}
add_action( 'plugins_loaded', 'yith_wcms_install', 11 );
