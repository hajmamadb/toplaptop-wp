<?php
/**
 * Global Functions
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/functions
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'challan_load_mpdf_lib_pro' ) ) {

	/**
	 * Load MPDF library
	 *
	 * @return bool
	 */
	function challan_load_mpdf_lib_pro() {
		$mpdf_class_exist = apply_filters('woo_challan_mpdf_class_exist', true, 'challan');

		if ( $mpdf_class_exist ) {
			if ( class_exists( '\Mpdf\Mpdf' ) || class_exists( \Mpdf\Mpdf::class ) ) {
				return true;
			}
		}

		if ( file_exists( CHALLAN_PRO_MPDF_LIB_DIR . "vendor/autoload.php" ) ) {
			require_once CHALLAN_PRO_MPDF_LIB_DIR . "vendor/autoload.php";

			return true;
		}

		return false;
	}

	challan_load_mpdf_lib_pro();

}


function woo_challan_mpdf_class_exist_cb( $exist, $plugin_name ) {
	return false;
}

if ( get_option( 'wpifw_disable_third_party_mpdf_library' ) ) {
	add_filter('woo_challan_mpdf_class_exist', 'woo_challan_mpdf_class_exist_cb', 10, 2);
}
