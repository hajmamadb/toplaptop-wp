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

if ( ! function_exists("challan_download_default_fonts_on_plugin_activate") ) {
    /**
     * Download default fonts on activate plugin
     */
    function challan_download_default_fonts_on_plugin_activate() {
        if ( ! class_exists("Challan_Pro_PluginDefaultFontDownloader") ) {
            require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . "includes/classes/class-plugin-default-font-downloader.php";
        }
        Challan_Pro_PluginDefaultFontDownloader::downloadDefaultFonts();
        flush_rewrite_rules();
    }

    register_activation_hook(CHALLAN_PRO_ROOT_FILE, 'challan_download_default_fonts_on_plugin_activate');
}
