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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( "challan_admin_notice_downloading_font_background_process_in_progress__info" ) ) {
	/**
	 * Show an admin notice if font downloading, MPDF library downloading is not complete and downloading is running in the background.
	 *
	 * @return void
	 */
	function challan_admin_notice_downloading_font_background_process_in_progress__info() {

		$mpdf_lib_downloaded     = get_option( "challan_mpdf_lib_downloaded", false );
		$default_font_downloaded = get_option( "challan_default_font_downloaded", false );
		if ( ! empty( $mpdf_lib_downloaded ) && ! empty( $default_font_downloaded ) ) {
			return '';
		}
		$class   = 'notice notice-info';
		$message = __( 'From Challan plugin, background process for downloading the fonts in progress for PDF invoice, Packing Slip, and delivery address.', 'webappick-pdf-invoice-for-woocommerce' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );

	}

	add_action( 'admin_notices', 'challan_admin_notice_downloading_font_background_process_in_progress__info' );

}