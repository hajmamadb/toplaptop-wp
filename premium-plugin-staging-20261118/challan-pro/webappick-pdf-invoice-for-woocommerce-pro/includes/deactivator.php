<?php
/**
 * Scripts for plugin deactivation hook
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/hooks
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function challan_pro_deactivate() {
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
    wp_cache_flush();
}
register_deactivation_hook( CHALLAN_PRO_ROOT_FILE, 'challan_pro_deactivate' );