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

if ( ! function_exists("challan_admin_notice_invoice_dir_is_not_writeable__error") ) {
    /**
     * Show an admin notice if wp-content/WOO-INVOICE directory is not writeable.
     *
     * @return void
     */
    function challan_admin_notice_invoice_dir_is_not_writeable__error() {
        $plugins = get_plugins();
        if ( ! is_writeable(WP_CONTENT_DIR) ) {
            $class = 'notice notice-error';
            $message = __('An error has occurred the directory of <strong style="color: red;">' . CHALLAN_PRO_INVOICE_DIR . '</strong> is not writeable. Challan plugin require read, write permission for this directory to store its assets such as Fonts, Invoice, Packing Slip, Delivery Address etc. Please contact with your system admin or server admin.', 'webappick-pdf-invoice-for-woocommerce');
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        }
    }

    add_action('admin_notices', 'challan_admin_notice_invoice_dir_is_not_writeable__error');

}


