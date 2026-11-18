<?php
/**
 * Data file downloader for MPDF from DropBox
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/library
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */

/**
 * Data files downloader for MPDF
 */
class Challan_Pro_PluginDataDatDownloader {

    /**
     * Download data files for MPDF
     *
     * @return  bool true|false
     */
    static public function downloadDataFiles(){
        if ( ! class_exists("Challan_Pro_DropBoxFontDownloader") ) {
            require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . "includes/classes/class-dropbox-font-downloader.php";
        }
        // default font downloader
        $fontConfig         = include CHALLAN_PRO_PLUGIN_ROOT_PATH . "includes/configs/config-fonts-dropbox.php"; // phpcs:ignore
        //$baseUrl            = implode("", $fontConfig['base-url']);
        $baseUrl            = $fontConfig['base-url'];
        $downloadCounter    = 0;
        foreach ( $fontConfig['data-files'] as $path ) {
            $source         = "{$baseUrl}{$path}";
            $fileSep        = explode('/', $source);
            $destination    = CHALLAN_PRO_FONT_DIR . $fileSep[ count($fileSep) - 1 ];
            if ( ! file_exists(rtrim($destination, ".zip")) ) {
                //Download data files using font downloader, font downloader will perform the data download task
                Challan_Pro_DropBoxFontDownloader::dispatchDownload("{$baseUrl}{$path}");
                ++$downloadCounter;
                if ( $downloadCounter % 4 == 0 ) {
                    sleep(1);
                }
            }
        }
        return true;
    }
}
