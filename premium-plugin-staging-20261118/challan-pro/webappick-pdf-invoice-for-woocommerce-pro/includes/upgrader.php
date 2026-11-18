<?php
/**
 * Scripts for plugin auto updater / upgrade hook.
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/hooks
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Load all the scripts those require on plugin update or upgrade
 */
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/upgrader/download-data-files-for-mpdf-on-plugin-upgrade.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/upgrader/download-mpdf-lib-on-plugin-upgrade.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/upgrader/download-default-fonts-for-mpdf-on-plugin-upgrade.php';
