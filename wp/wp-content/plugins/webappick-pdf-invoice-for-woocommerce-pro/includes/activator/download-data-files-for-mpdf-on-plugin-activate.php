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


if ( ! function_exists("challan_download_data_dat_file_on_plugin_activate") ) {
    /**
     * Download data .dat file for MPDF
     */
    function challan_download_data_dat_file_on_plugin_activate() {
        if ( ! class_exists("Challan_Pro_PluginDataDatDownloader") ) {
            require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . "includes/classes/class-plugin-font-data-dat-downloader.php";
        }
        Challan_Pro_PluginDataDatDownloader::downloadDataFiles();
        flush_rewrite_rules();
    }

    register_activation_hook(CHALLAN_PRO_ROOT_FILE, 'challan_download_data_dat_file_on_plugin_activate');
}
