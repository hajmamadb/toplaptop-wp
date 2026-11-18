<?php
/**
 * Plugin constant and variable declarations
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/configuration
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! defined( 'CHALLAN_PRO_ADMIN_PATH' ) ) {
    /**
     * Admin File Path with trailing slash.
     *
     * @var string
     */
    define( 'CHALLAN_PRO_ADMIN_PATH', CHALLAN_PRO_PATH . 'admin/' );
}
if ( ! defined( 'CHALLAN_PRO_LIBS_PATH' ) ) {
    /**
     * Admin File Path with trailing slash
     *
     * @var string
     */
    define( 'CHALLAN_PRO_LIBS_PATH', CHALLAN_PRO_PATH . 'libs/' );
}
if ( ! defined( 'CHALLAN_PRO_PLUGIN_URL' ) ) {
    /**
     * Plugin Directory URL.
     *
     * @var string
     * @since 1.2.2
     */
    define( 'CHALLAN_PRO_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}
if ( ! defined( 'CHALLAN_PRO_PLUGIN_BASE_NAME' ) ) {
    /**
     * Plugin Base name..
     *
     * @var string
     * @since 1.2.2
     */
    define( 'CHALLAN_PRO_PLUGIN_BASE_NAME', plugin_basename( __FILE__ ) );
}


if ( ! defined( 'CHALLAN_PRO_TEMP_DIR' ) ) {
    /**
     * Custom tempDir Directory for MPDF
     *
     * @var string
     * @since 2.3.1
     */
    //e.g dir like /WOO-INVOICE/WOO-INVOICE/TEMP
    define( 'CHALLAN_PRO_TEMP_DIR', wp_upload_dir()['basedir'] ."/WOO-INVOICE/TEMP/" );

}


if ( ! defined( 'CHALLAN_PRO_ADMIN_UI_FONTS_DIR' ) ) {
    /**
     * Custom fonts' directory for admin ui related fonts
     *
     * @var string
     * @since 2.3.1
     */
    //e.g dir like /uploads/WOO-INVOICE-ADMIN-UI-FONTS/
    define( 'CHALLAN_PRO_ADMIN_UI_FONTS_DIR', wp_upload_dir()['basedir'] ."/WOO-INVOICE-ADMIN-UI-FONTS/" );

}

if ( ! defined( 'CHALLAN_PRO_MPDF_LIB_DIR' ) ) {
	/**
	 * Custom fonts' directory for admin ui related fonts
	 *
	 * @var string
	 * @since 4.1.27
	 */
	//e.g dir like /uploads/WOO-INVOICE/mpdf-pro
	define( 'CHALLAN_PRO_MPDF_LIB_DIR', CHALLAN_PRO_INVOICE_DIR ."mpdf-pro/" );

}