<?php
/**
 * Scripts for showing notices
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/notices
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! function_exists("challan_admin_notice_woocommerce_is_not_installed__error") ) {
    /**
     * Show an admin notice if WooCommerce is not installed.
     *
     * @return void
     */
    function challan_admin_notice_woocommerce_is_not_installed__error() {
		$plugins = get_plugins();
		$woocommerce = isset($plugins["woocommerce/woocommerce.php"]) && is_plugin_active("woocommerce/woocommerce.php") ? $plugins["woocommerce/woocommerce.php"] : [];
		if ( empty($woocommerce) ) {
			$class = 'notice notice-error';
			$woocommercePluginLink = '<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>';
			$message = __("<b>Challan</b> plugin has a peer dependency of $woocommercePluginLink plugin but $woocommercePluginLink plugin is not installed. Please install $woocommercePluginLink plugin to enjoy the awesome features of the <b>Challan</b> plugin.", 'webappick-pdf-invoice-for-woocommerce');

			if ( isset($plugins["woocommerce/woocommerce.php"]) && ! is_plugin_active("woocommerce/woocommerce.php") ) {
				$message = __("<b>Challan</b> plugin has a peer dependency of $woocommercePluginLink plugin but $woocommercePluginLink plugin is not activated. Please active $woocommercePluginLink plugin to enjoy the awesome features of the <b>Challan</b> plugin.", 'webappick-pdf-invoice-for-woocommerce');
			}

			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
		}
    }

    add_action('admin_notices', 'challan_admin_notice_woocommerce_is_not_installed__error');

}
