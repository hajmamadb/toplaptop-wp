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
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * Load MPDF library
 *
 * @return bool
 */
function challan_load_mpdf_lib() {
    if ( class_exists('\Mpdf\Mpdf') || class_exists(\Mpdf\Mpdf::class) ) {
        return true;
    }
    if ( file_exists(CHALLAN_PRO_INVOICE_DIR . "mpdf/vendor/autoload.php") ) {
        require_once CHALLAN_PRO_INVOICE_DIR . "mpdf/vendor/autoload.php";
        return true;
    }
    return false;
}
challan_load_mpdf_lib();