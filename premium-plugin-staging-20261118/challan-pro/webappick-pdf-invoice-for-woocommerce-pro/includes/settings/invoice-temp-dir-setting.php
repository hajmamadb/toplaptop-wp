<?php
/**
 * Scripts for plugin settings
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/settings
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
// If this file is called directly, abort.
if ( ! defined('ABSPATH') ) {
    exit;
}

if ( ! function_exists("challan_woo_invoice_temp_dir_setting") ) {
    /**
     * Temp directory checker for MPDF.
     *
     * @return void
     */
    function challan_woo_invoice_temp_dir_setting() {
        if ( defined('CHALLAN_PRO_TEMP_DIR') ) {
            /**
             * Setup Custom tempDir Directory for MPDF
             *
             * @var string.
             * @since 2.3.1
             */
            //Make dir like /WOO-INVOICE/TEMP
            if ( ! file_exists(rtrim(CHALLAN_PRO_TEMP_DIR, "/")) ) {
                mkdir(rtrim(CHALLAN_PRO_TEMP_DIR, "/"), 0777, true);
            }
            if ( is_writable(rtrim(CHALLAN_PRO_TEMP_DIR, "/")) ) {
                if ( ! file_exists(CHALLAN_PRO_TEMP_DIR . '.htaccess') ) {
                    // Protect files from public access.
                    touch(CHALLAN_PRO_TEMP_DIR . '.htaccess');
                    $content = 'deny from all';
                    $fp = fopen(CHALLAN_PRO_TEMP_DIR . '.htaccess', 'wb');
                    fwrite($fp, $content);
                    fclose($fp);
                }
            }

            //Make dir like /WOO-INVOICE/TEMP/ttfontdata
            if ( ! file_exists(CHALLAN_PRO_TEMP_DIR . "ttfontdata") ) {
                mkdir(CHALLAN_PRO_TEMP_DIR . "ttfontdata", 0777, true);
            }
            if ( is_writeable(CHALLAN_PRO_TEMP_DIR . "ttfontdata") ) {
                if ( ! file_exists(CHALLAN_PRO_TEMP_DIR . "ttfontdata/" . '.htaccess') ) {
                    // Protect files from public access.
                    touch(CHALLAN_PRO_TEMP_DIR . "ttfontdata/" . '.htaccess');
                    $content = 'deny from all';
                    $fp = fopen(CHALLAN_PRO_TEMP_DIR . "ttfontdata/" . '.htaccess', 'wb');
                    fwrite($fp, $content);
                    fclose($fp);
                }
            }
        }
    }

    //call immediate
    challan_woo_invoice_temp_dir_setting();
}

