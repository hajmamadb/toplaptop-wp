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
 * Download MPDF library
 */
register_activation_hook(CHALLAN_PRO_ROOT_FILE, function () {
    Challan_Pro_MpdfLibDownloader::downloadLib();
});
