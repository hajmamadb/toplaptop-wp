<?php
/**
 * Scripts for plugin settings
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/settings
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined('ABSPATH') ) {
    exit;
}


/**
 * Admin Notice if not writable uploads directory.
 */
function challan_pro_font_dir_notice() { ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <?php
            _e('<h1>Challan</h1>  <b>Your uploads folder is not writable. Please make <code style="color: #ff0000;">wp-content/uploads</code> folder writable to generate and save invoices.</b>', 'webappick-pdf-invoice-for-woocommerce'); //phpcs:ignore
            ?>
        </p>
    </div> <?php
}

if ( ! woo_invoice_pro_is_uploads_folder_writable() ) {
    add_action('admin_notices', 'challan_pro_font_dir_notice');
}

/**
 * Load all the scripts those require for settings
 */
//require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/settings/invoice-temp-dir-setting.php';
//require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/settings/admin-ui-fonts-dir-setting.php';