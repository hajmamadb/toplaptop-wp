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
 * MPDF library downloader
 */
class Challan_Pro_MpdfLibDownloader {

    static public function hasDownloaded(){
        return file_exists(CHALLAN_PRO_MPDF_LIB_DIR . "vendor/autoload.php");
    }
    static public function deleteChallanFreeMpdfDir( $dir ) {
        $files = array_diff(scandir($dir), array( '.', '..' ));

        foreach ( $files as $file ) {
            (is_dir("$dir/$file")) ? self::deleteChallanFreeMpdfDir("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
    /**
     * Download data files for MPDF
     *
     * @return  bool true|false
     */
    static public function downloadLib(){

        // Delete mpdf free version directory if it is exists.
        if ( file_exists( CHALLAN_PRO_INVOICE_DIR . "mpdf/vendor/autoload.php" ) ) {
            self::deleteChallanFreeMpdfDir( CHALLAN_PRO_INVOICE_DIR . 'mpdf/' );
        }
        if ( ! woo_invoice_pro_is_uploads_folder_writable() ) {
            return;
        }
        //has downloaded already
        if ( self::hasDownloaded() ) {
            return true;
        }
        // default font downloader
        $fontConfig     = include CHALLAN_PRO_PLUGIN_ROOT_PATH . "includes/configs/config-fonts-dropbox.php"; // phpcs:ignore
        //$baseUrl        = implode("", $fontConfig['base-url']);
        $baseUrl        = $fontConfig['base-url'];
        $path           = $fontConfig['mpdf-lib'];
        $source         = "{$baseUrl}{$path}";
        $fileSep        = explode('/', $source);
        $destination    = CHALLAN_PRO_INVOICE_DIR . $fileSep[ count($fileSep) - 1 ];
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );
        $data = file_get_contents($source, false, stream_context_create( $arrContextOptions ) );
        //download file
        if ( file_put_contents($destination, $data ) ) {
            $extractTo  = CHALLAN_PRO_INVOICE_DIR;
            $zipFile    = $destination;
            $zip        = new ZipArchive();
            $res        = $zip->open($zipFile);
            if ( $res === TRUE ) {
                $zip->extractTo($extractTo);
                $zip->close();
                if ( file_exists( $zipFile ) ) {
                    unlink( $zipFile );
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
