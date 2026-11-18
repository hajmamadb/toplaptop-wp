<?php

/**
 * Font downloader for DropBox
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/library
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
class Challan_Pro_DropBoxFontDownloader
{
    /**
     * Rename file if contains html encoded space
     *
     * @param   $filePath
     * @return  void
     */
    static public function maybeRequireRename( $filePath ) {
        $fileSep    = explode('/', $filePath);
        $fileName   = $fileSep[ count($fileSep) - 1 ];
        if ( strpos($fileName, '%20') ) {
            $newFileName = str_replace('%20', ' ', $fileName);
            $newFilePath = CHALLAN_PRO_FONT_DIR . $newFileName;
//            rename($filePath, $newFilePath);
            copy($filePath, $newFilePath);
        }
    }

    /**
     * Dispatch font download process
     *
     * @param string $source
     * @param string $dest
     * @return  bool    true|false
     */
    static public function dispatchDownload( $source, $dest = '' ) {
        $fileSep = explode('/', $source);
        $destination = ! empty($dest) ? $dest : CHALLAN_PRO_FONT_DIR . $fileSep[ count($fileSep) - 1 ];

        //download font
        $downloadSuccess = self::downloadFont($source, $destination);

        if ( $downloadSuccess ) {
            if ( self::isZipArchiveFont($destination) ) {
                //then extract zip file
                if ( self::extractZipArchiveFont($destination, CHALLAN_PRO_FONT_DIR) ) {
//                    self::maybeRequireRename($destination);
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Download font file from DropBox. If download being success return true either false
     *
     * @param string $source
     * @param string $destination
     * @return  bool      true|false
     */
    static public function downloadFont( $source, $destination ) {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );
        $data = file_get_contents($source, false, stream_context_create( $arrContextOptions ) );
        if ( file_put_contents($destination, $data) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check source file is a zip archive file or not.
     *
     * @param string $file
     * @return  bool    true|false
     */
    static public function isZipArchiveFont( $file ) {
        return $file && strpos($file, '.zip');
    }

    /**
     * Extract zip file to destination directory.
     *
     * @param string $zipFile
     * @param string $extractTo
     * @return  bool    true|false
     */
    static public function extractZipArchiveFont( $zipFile, $extractTo ) {
        $zip = new ZipArchive();
        $res = $zip->open($zipFile);
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
    }

    /**
     * Prepare download configuration for MPDF
     *
     * @param   $return
     * @return  mixed|void
     */
    static public function prepareConfigForDownloadFont( $return = true ) {
        $fontConfig = include CHALLAN_PRO_PLUGIN_ROOT_PATH . "includes/configs/config-fonts-dropbox.php";
        $fonts      = $fontConfig["others"];
        $downloaded = [];
        $download   = [];
        foreach ( $fonts as $font ) {
            $fileSep = explode('/', $font);
            $destination = CHALLAN_PRO_FONT_DIR . $fileSep[ count($fileSep) - 1 ];
            if ( file_exists(rtrim($destination, ".zip")) ) {
                $downloaded[] = $font;
            } else {
                $download[] = $font;
            }
        }
        $fontConfig["downloaded"]       = $downloaded;
        $fontConfig["download"]         = $download;
        $fontConfig["font_downloaded"]  = count($downloaded);
        $fontConfig["font_remaining"]   = count($download);
        $fontConfig["font_total"]       = count($fontConfig["others"]);
        if ( $return ) {
            return $fontConfig;
        }
    }
}
