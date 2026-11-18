<?php
/**36
 * Plugin Name: YITH WooCommerce Checkout Manager - نوین وردپرس
 * Plugin URI: https://wpnovin.com/product/yith-woocommerce-checkout-manager-wordpress-plugin/
 * Description: <code><strong>#378716</strong></code> افزونه ویرایش فیلدهای تسویه حساب ووکامرس
 * Version: 1.61.0
 * Author: w w w . W P N O V I N . c o m
 * Author URI: https://wpnovin.com/product/yith-woocommerce-checkout-manager-wordpress-plugin/
 * Text Domain: yith-woocommerce-checkout-manager
 * Domain Path: /languages/
 */
 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Message is WooCommerce is not installed
 *
 * @since 1.0.0
 * @return void
 */
function ywccp_install_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php esc_html_e( 'YITH WooCommerce Checkout Manager is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-checkout-manager' ); ?></p>
	</div>
	<?php
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );


if ( ! defined( 'YWCCP_VERSION' ) ) {
	define( 'YWCCP_VERSION', '1.61.0' );
}
if ( ! defined( 'YWCCP_INIT' ) ) {
	define( 'YWCCP_INIT', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'YWCCP' ) ) {
	define( 'YWCCP', true );
}
if ( ! defined( 'YWCCP_FILE' ) ) {
	define( 'YWCCP_FILE', __FILE__ );
}
if ( ! defined( 'YWCCP_URL' ) ) {
	define( 'YWCCP_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'YWCCP_DIR' ) ) {
	define( 'YWCCP_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'YWCCP_TEMPLATE_PATH' ) ) {
	define( 'YWCCP_TEMPLATE_PATH', YWCCP_DIR . 'templates' );
}
if ( ! defined( 'YWCCP_ASSETS_URL' ) ) {
	define( 'YWCCP_ASSETS_URL', YWCCP_URL . 'assets' );
}
if ( ! defined( 'YWCCP_SLUG' ) ) {
	define( 'YWCCP_SLUG', 'yith-woocommerce-checkout-manager' );
}
if ( ! defined( 'YWCCP_SECRET_KEY' ) ) {
	define( 'YWCCP_SECRET_KEY', 'nwlgveplC1HthBsoJnEO' );
}
 

/**
 * Init.
 *
 * @since 1.0.0
 * @return void
 */
function ywccp_init() {

	load_plugin_textdomain( 'yith-woocommerce-checkout-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	require_once 'includes/functions.ywccp.php';
	require_once 'includes/hooks.ywccp.php';
	require_once 'includes/class.ywccp.php';

	// Start the game!
	YWCCP();
}

add_action( 'ywccp_init', 'ywccp_init' );

/**
 * Install.
 *
 * @since 1.0.0
 * @return void
 */
function ywccp_install() {

	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'ywccp_install_woocommerce_admin_notice' );
	} else {
		do_action( 'ywccp_init' );
	}
}

add_action( 'plugins_loaded', 'ywccp_install', 11 );

if ( ! function_exists( 'ywccp_activation_plugin_action' ) ) {
	/**
	 * On activation restore order meta fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function ywccp_activation_plugin_action() {
		global $wpdb;

		if ( ! function_exists( 'ywccp_get_custom_fields' ) ) {
			require_once 'includes/functions.ywccp.php';
		}

		$billing_fields = ywccp_get_custom_fields( 'billing' );
		$billing_fields = empty( $billing_fields ) ? array() : array_keys( $billing_fields );

		$shipping_fields = ywccp_get_custom_fields( 'shipping' );
		$shipping_fields = empty( $shipping_fields ) ? array() : array_keys( $shipping_fields );

		$fields = array_merge( $shipping_fields, $billing_fields );

		if ( empty( $fields ) ) {
			return;
		}

		$fields = implode( "','", $fields );

		$query = "UPDATE $wpdb->postmeta SET meta_key = CONCAT( '_', meta_key ) WHERE meta_key IN ('$fields')";

		$wpdb->query( $query ); // phpcs:ignore
	}
}
register_activation_hook( __FILE__, 'ywccp_activation_plugin_action' );

if ( ! function_exists( 'ywccp_deactivation_plugin_action' ) ) {
	/**
	 * On deactivation hooks register meta for order
	 *
	 * @since 1.0.0
	 */
	function ywccp_deactivation_plugin_action() {

		global $wpdb;

		if ( ! function_exists( 'ywccp_get_custom_fields' ) ) {
			require_once 'includes/functions.ywccp.php';
		}

		$billing_fields = ywccp_get_custom_fields( 'billing' );
		$billing_fields = empty( $billing_fields ) ? array() : array_keys( $billing_fields );

		$shipping_fields = ywccp_get_custom_fields( 'shipping' );
		$shipping_fields = empty( $shipping_fields ) ? array() : array_keys( $shipping_fields );

		$fields = array_merge( $shipping_fields, $billing_fields );

		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $fields as &$field ) {
			$field = '_' . $field;
		}

		$fields = implode( "','", $fields );

		$query = "UPDATE $wpdb->postmeta SET meta_key = SUBSTRING( meta_key, 2) WHERE meta_key IN ('$fields')";

		$wpdb->query( $query ); // phpcs:ignore
	}
}
register_deactivation_hook( __FILE__, 'ywccp_deactivation_plugin_action' );

if ( ! function_exists( 'yith_wcaf_maybe_load_plugin_fw' ) ) {
  /**
   * Check plugin framework version.
   *
   * @return void.
   */
  function yith_wcaf_maybe_load_plugin_fw() {
    // Plugin Framework Loader.
    if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
      require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
    }

    // activation hook.
    if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
      require_once 'plugin-fw/yit-plugin-registration-hook.php';
    }
    register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

    if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
      include_once 'plugin-upgrade/functions-yith-licence.php';
    }
    register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );
  }
}

// load plugin-fw.
yith_wcaf_maybe_load_plugin_fw();