<?php
/**
 * Scripts for plugin activation hook
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/hooks
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined('ABSPATH') ) {
    exit;
}


/**
 * Download MPDF library. Register hook for plugin update / auto-update / upgrade.
 */
add_action('upgrader_process_complete', function ( $upgrader_object, $options ) {

    $current_plugin_path_name = plugin_basename(CHALLAN_PRO_ROOT_FILE);
    if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
        foreach ( $options['plugins'] as $each_plugin ) {
            if ( $each_plugin == $current_plugin_path_name ) {
                Challan_Pro_MpdfLibDownloader::downloadLib();
                //download data files
                flush_rewrite_rules();
            }
        }
    }
}, 10, 2);

//phpcs:ignore
//manually trigger a hook:upgrader_process_complete
//do_action( 'upgrader_process_complete', [], ['action' => "update", "type" => "plugin", "plugins" => ["webappick-pdf-invoice-for-woocommerce-pro/challan-pro.php"]]);



if ( ! function_exists('challan_maybe_require_mpdf_lib_download_double_checker') ) {

    /**
     * Double check MPDF library has been downloaded, plugin on install or update.
     *
     * @return bool
     * @since  4.1.27
     */
    function challan_maybe_require_mpdf_lib_download_double_checker() {
        if ( ! function_exists('get_plugin_data') ) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $plugin_version = get_plugin_data(CHALLAN_PRO_ROOT_FILE)["Version"];
        if ( version_compare($plugin_version, '3.3.24') >= 0 ) {
            if ( ! get_option("challan_mpdf_lib_downloaded", false) ) {
                //download lib
                Challan_Pro_MpdfLibDownloader::downloadLib();
                update_option("challan_mpdf_lib_downloaded", true);
                flush_rewrite_rules();
                return true;
            }
        }
        return false;
    }
    /**
     * Call MPDF lib double checker
     */
    if ( is_admin() ) {
        challan_maybe_require_mpdf_lib_download_double_checker();
    }
}






