<?php
/**
 * Fonts downloader for admin ui from DropBox
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/library
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */

/**
 * Admin UI font downloader
 */
class Challan_Pro_AdminUIFontDownloader
{

    /**
     * Font configuration form config file
     *
     * @var array
     */
    static public $fontConfig = [];

    /**
     * Download data files for MPDF
     *
     * @return  bool true|false
     */
    static public function downloadFont() {
        //has already been downloaded
        if ( self::hasDownloaded() ) {
            return true;
        }
        // default font downloader
        $fontConfig     = self::getConfig();
       // $baseUrl        = implode("", $fontConfig['base-url']);
        $baseUrl        = $fontConfig['base-url'];
        $path           = $fontConfig['admin-ui-fonts'];
        $source         = "{$baseUrl}{$path}";
        $fileSep        = explode('/', $source);
        $destination    = CHALLAN_PRO_ADMIN_UI_FONTS_DIR . $fileSep[ count($fileSep) - 1 ];
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );
        $data = file_get_contents($source, false, stream_context_create( $arrContextOptions ) );
        //download file
        if ( file_put_contents($destination, $data ) ) {
            $extractTo  = CHALLAN_PRO_ADMIN_UI_FONTS_DIR;
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

    /**
     * Check fonts already downloaded or not
     *
     * @return bool
     */
    static public function hasDownloaded() {
        $fontConfig = self::getConfig();
        foreach ( $fontConfig['admin-ui-font-list'] as $fontpath ) {
            if ( file_exists(CHALLAN_PRO_ADMIN_UI_FONTS_DIR . $fontpath) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load and get default config
     *
     * @return array|mixed
     */
    static public function getConfig() {
        if ( empty(self::$fontConfig) ) {
            self::$fontConfig = include CHALLAN_PRO_PLUGIN_ROOT_PATH . "includes/configs/config-fonts-dropbox.php"; // phpcs:ignore
        }
        return self::$fontConfig;
    }
}
