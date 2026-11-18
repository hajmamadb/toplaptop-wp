<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://webappick.com
 * @since             1.0.0
 * @package           Woo_Invoice_Pro
 *
 * @wordpress-plugin
 * Plugin Name:  Challan Pro
 * Plugin URI:   https://webappick.com
 * Description:  Automatically email PDF invoice and easily create & print packing slip and shipping label for woocommerce orders.
 * Version:      4.5.14
 * Author:       نوین وردپرس
 * Author URI:   wpnovin.com
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  webappick-pdf-invoice-for-woocommerce
 * Domain Path:  /languages
 *
 * WP Requirement & Test
 * Requires at least: 4.4
 * WC Requirement & Test
 * WC requires at least: 3.2
 * WC tested up to: 10.3
 * Requires Plugins: woocommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */


define( 'CHALLAN_PRO_VERSION', '4.5.14' );


if ( ! defined( 'CHALLAN_PRO_ROOT_FILE' ) ) {
    /**
     * Plugin main file
     *
     * @since 4.1.27
     * @var string
     */
    define( 'CHALLAN_PRO_ROOT_FILE', __FILE__ );
}
if ( ! defined( 'CHALLAN_PRO_FONTS_COUNT' ) ) {
    /**
     * Count mPDF Fonts
     *
     * @var string dirname( __FILE__ )
     */
    define( 'CHALLAN_PRO_FONTS_COUNT', '42' );
}
if ( ! defined( 'CHALLAN_PRO_PATH' ) ) {
    /**
     * Plugin Path with trailing slash
     *
     * @var string dirname( __FILE__ )
     */
    define( 'CHALLAN_PRO_PATH', plugin_dir_path( __FILE__ ) );
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
if ( ! defined( 'CHALLAN_PRO_BASE_NAME' ) ) {
    /**
     * Plugin Base name..
     *
     * @var string
     * @since 1.2.2
     */
    define( 'CHALLAN_PRO_BASE_NAME', plugin_basename( __FILE__ ) );
}

// is uploads folder writable
function woo_invoice_pro_is_uploads_folder_writable() {
    $upload_dir             = wp_upload_dir();
    $base_dir               = $upload_dir['basedir'];

    if ( is_writable( $base_dir ) ) {
        return true;
    }
    return false;
}



if ( ! defined( 'CHALLAN_PRO_INVOICE_DIR' ) ) {
    /**
     * Custom Font Directory.
     *
     * @var string.
     * @since 2.3.1
     */
    $upload_dir        = wp_upload_dir();
    $base_dir          = $upload_dir['basedir'];
    $wpifw_invoice_dir = $base_dir."/WOO-INVOICE";
    define( 'CHALLAN_PRO_INVOICE_DIR', $wpifw_invoice_dir . '/' );
    if ( woo_invoice_pro_is_uploads_folder_writable() ) {
        if ( ! file_exists(CHALLAN_PRO_INVOICE_DIR.'.htaccess' ) ) {
            mkdir( CHALLAN_PRO_INVOICE_DIR, 0777, true );
            // Protect files from public access.
            $content = 'deny from all';
            $fp = fopen(CHALLAN_PRO_INVOICE_DIR . '.htaccess', 'wb');//phpcs:ignore
            fwrite($fp, $content);//phpcs:ignore
            fclose($fp);//phpcs:ignore
        }
    }
}

if ( ! defined( 'CHALLAN_PRO_FONT_DIR' ) ) {
    /**
     * Custom Font Directory.
     *
     * @var string
     * @since 2.3.1
     */
    $upload_dir             = wp_upload_dir();
    $base_dir               = $upload_dir['basedir'];
    $wpifw_invoice_font_dir = CHALLAN_PRO_INVOICE_DIR."WOO-INVOICE-FONTS";
    define( 'CHALLAN_PRO_FONT_DIR', $wpifw_invoice_font_dir . '/' );
    if ( woo_invoice_pro_is_uploads_folder_writable() ) {
        if ( ! file_exists(CHALLAN_PRO_FONT_DIR ) ) {
            mkdir( $wpifw_invoice_font_dir, 0777, true );
            // Protect files from public access.
            $content = 'deny from all';
            $fp      = fopen( CHALLAN_PRO_FONT_DIR . '.htaccess', 'wb' );//phpcs:ignore
            fwrite( $fp, $content );//phpcs:ignore
            fclose( $fp );//phpcs:ignore
        }
    }
}


if ( ! defined( 'CHALLAN_PRO_PATH' ) ) {
    /**
     * Plugin Path with trailing slash
     *
     *
     * @since 4.1.27
     * @var string dirname( __FILE__ )
     */
    define( 'CHALLAN_PRO_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CHALLAN_PRO_PLUGIN_ROOT_PATH' ) ) {
    /**
     * Plugin Root Path with trailing slash
     *
     * @since 4.1.27
     * @var string dirname( __FILE__ )
     */
    define( 'CHALLAN_PRO_PLUGIN_ROOT_PATH', plugin_dir_path( __FILE__ ) );
}


require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/constants.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/config.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/settings.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/functions.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/classes.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/activator.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/deactivator.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/uninstaller.php';
function challan_pro_load_upgrader_file()
{
    require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/upgrader.php';
}
// Hook into `init` to ensure files are only loaded after WordPress initialization.
add_action('init', 'challan_pro_load_upgrader_file');

require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/notices.php';
require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/filters.php';

//Load MPDF library
if ( function_exists( 'challan_load_mpdf_lib_pro' ) ) {
    challan_load_mpdf_lib_pro();
}

require_once CHALLAN_PRO_PLUGIN_ROOT_PATH . 'includes/rest-api/font-downloader-api.php';


/**
 * WebAppick Service API
 */
require CHALLAN_PRO_PATH . 'includes/class-woo-invoice-webappick-api.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require CHALLAN_PRO_PATH . 'includes/class-woo-invoice-pro.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function woo_invoice_pro_run() {
    // Deactivate free version.
    woo_invoice_pro_deactivate_free();
    // Woo_Invoice_ProWebAppickAPI::getInstance();

    // Initialize the main plugin functionality
    $plugin = new Woo_Invoice_Pro();
    $plugin->run();

    //HPOS compatibility
    if( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        add_action( 'before_woocommerce_init', function () {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            }
        } );
    }

}

woo_invoice_pro_run();

function woo_invoice_pro_web_appick_api() {
    Woo_Invoice_ProWebAppickAPI::getInstance();
}

add_action('init', 'woo_invoice_pro_web_appick_api',1);

/**
 *  Load Plugin settings page, process and save setting
 */
function woo_invoice_pro() {
    // Process settings form data and update.
    if ( isset( $_POST['wpifw_submit'] ) ) {
        if ( isset( $_POST['wpifw_data_collection'] ) && true === $_POST['wpifw_data_collection'] ) {
            Woo_Invoice_ProWebAppickAPI::getInstance()->trackerOptIn( true );
        } else {
            Woo_Invoice_ProWebAppickAPI::getInstance()->trackerOptOut();
        }

        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }
        if ( isset( $_POST['wpifw_download'] ) && isset( $_POST['wpifw_invoice_download_check_list'] ) ) {
            $download_check_list = array();
            foreach ( $_POST['wpifw_invoice_download_check_list'] as $key => $value ) { //phpcs:ignore
                $download_check_list[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            }
            update_option( 'wpifw_invoice_download_check_list', $download_check_list );
        } else {
            update_option( 'wpifw_invoice_download_check_list', array() );
        }
        // Update attach email information.
        if ( isset( $_POST['wpifw_order_email'] ) && isset( $_POST['wpifw_email_attach_check_list'] ) ) {
            $email_check_list = array();
            foreach ( $_POST['wpifw_email_attach_check_list'] as $key => $value ) { //phpcs:ignore
                $email_check_list[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            }
            update_option( 'wpifw_email_attach_check_list', $email_check_list );
        } else {
            update_option( 'wpifw_email_attach_check_list', array() );
        }

        // Update attach packing slip email information.
        if ( isset( $_POST['wpifw_email_packing_slip'] ) && isset( $_POST['wpifw_email_attach_packing_slip_list'] ) ) {
            $packing_slip_check_list = array();
            foreach ( $_POST['wpifw_email_attach_packing_slip_list'] as $key => $value ) { //phpcs:ignore
                $packing_slip_check_list[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            }
            update_option( 'wpifw_email_attach_packing_slip_list', $packing_slip_check_list );
        } else {
            update_option( 'wpifw_email_attach_packing_slip_list', array() );
        }

        // echo count($_POST['wpifw_invoice_can_generate'] );exit;
        // Update Invoice can generate.
        if ( isset( $_POST['wpifw_invoice_can_generate'] ) && count($_POST['wpifw_invoice_can_generate'] ) > 0 ) {
            $invoice_can_generate = array();
            foreach ( $_POST['wpifw_invoice_can_generate'] as $key => $value ) { //phpcs:ignore
                $invoice_can_generate[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            }
            update_option( 'wpifw_invoice_can_generate', $invoice_can_generate );
        } else {
            update_option( 'wpifw_invoice_can_generate', array() );
        }
        // Update add page number information.
        if ( isset( $_POST['wpifw_enable_page_number'] ) && isset( $_POST['wpifw_add_page_number'] ) ) {
            $page_number_list = array();
            foreach ( $_POST['wpifw_add_page_number'] as $key => $value ) { //phpcs:ignore
                $page_number_list[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            }
            update_option('wpifw_add_page_number', $page_number_list);
        }else {
            update_option( 'wpifw_add_page_number', array() );
        }

        foreach ( $_POST as $key => $value ) {
            if ( 'wpifw_invoice_download_check_list' !== $key
                && 'wpifw_email_attach_check_list' !== $key
                && 'wpifw_email_attach_packing_slip_list' !== $key
                && 'wpifw_add_page_number' !== $key
                && 'wpifw_invoice_can_generate' !== $key ) {
                update_option( $key, sanitize_text_field( $value ) );
            }
        }
    }



    // settings for invoice.
    if ( isset( $_POST['wpifw_submit_invoice_section'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }
        $meta_array       = array();
        $order_item_meta_array = array();

        foreach ( $_POST as $key => $value ) {
            // if order meta label is empty then related all array are empty.
            if ( ! array_key_exists('_winvoice_order_meta_label', $_POST) ) {
                update_option( '_winvoice_order_meta_label', array() );
                update_option( '_winvoice_order_meta_name', array() );
                update_option( '_winvoice_order_meta_name_position', array() );
            }
            if ( 'wpifw_custom_stamp_attachment_id' === $key ) {
                $fullsize_path = get_attached_file( $value );
                update_option( $key, $fullsize_path );
                update_option( 'wpifw_custom_stamp_attachment_id', $value );
            }elseif ( 'wpifw_signature_attachment_id' === $key ) {
                $fullsize_path = get_attached_file( $value );
                update_option( $key, $fullsize_path );
                update_option( 'wpifw_signature_attachment_image_id', $value );
            } elseif ( 'wpifw_invoice_background_attachment_id' === $key ) {
                $fullsize_path = get_attached_file( $value );
                update_option( $key, $fullsize_path );
                update_option( 'wpifw_invoice_background_attachment_image_id', $value );
            }else {
                if ( 'wpifw_product_attribute_show' === $key ) {
                    continue;
                }elseif ( strpos( $key, '_winvoice_post_meta_label' ) !== false ) {
                    $meta = substr( $key, 0, strpos( $key, '_winvoice_post_meta_label' ) );
                    if ( '' !== $meta && '' !== $value ) {
                        $meta_array[ $meta ] = $value;
                    }
                }elseif ( strpos( $key, '_winvoice_post_meta_name' ) !== false ) {
                    continue;
                } elseif ( '_winvoice_order_meta_label' == $key ) {
                    $label = array();
                    $name = array();
                    $place = array();
                    $label_arr  = isset($_POST['_winvoice_order_meta_label']) ? $_POST['_winvoice_order_meta_label'] : array(); //phpcs:ignore
                    $name_arr   = isset($_POST['_winvoice_order_meta_name']) ? $_POST['_winvoice_order_meta_name'] : array(); //phpcs:ignore
                    $place_arr  = isset($_POST['_winvoice_order_meta_name_position']) ? $_POST['_winvoice_order_meta_name_position'] : array(); //phpcs:ignore
                    foreach ( $label_arr as $index => $val ) {
                        if ( '' != $val && '' != $name_arr[ $index ] && '' != $place_arr[ $index ] ) {
                            array_push($label, $val);
                            array_push($name, $name_arr[ $index ]);
                            array_push($place, $place_arr[ $index ]);
                        }else {
                            $label_arr[ $index ] = '';
                            $name_arr[ $index ] = '';
                            $place_arr[ $index ] = '';
                        }
                    }
                    if ( count($label) > 0 ) {
                        update_option( $key, $label );
                        update_option( '_winvoice_order_meta_name', $name );
                        update_option( '_winvoice_order_meta_name_position', $place );
                    }
                    else {
                        update_option( '_winvoice_order_meta_label', array() );
                        update_option( '_winvoice_order_meta_name', array() );
                        update_option( '_winvoice_order_meta_name_position', array() );
                    }
                }elseif ( strpos( $key, '_winvoice_order_item_meta_label' ) !== false ) {
                    $meta = substr( $key, 0, strpos( $key, '_winvoice_order_item_meta_label' ) );
                    if ( '' !== $meta && '' !== $value ) {
                        $order_item_meta_array[ $meta ] = $value;
                    }
                } elseif ( strpos( $key, '_winvoice_order_item_meta_name' ) !== false ) {
                    continue;
                }
                else {
                    if ( '_winvoice_order_meta_name_position' == $key || '_winvoice_order_meta_name' == $key ) {
                    }else {
                        update_option( $key, sanitize_text_field( $value ) );
                    }
                }
            }
        }
        update_option( 'wpifw_custom_post_meta', $meta_array );
        update_option( 'wpifw_order_item_meta', $order_item_meta_array );
    }

    // Packing Slip form submitted.
    if ( isset( $_POST['wpifw_submit_packingslip_section'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }
        $meta_array       = array();
        $order_item_meta_array_ps = array();
        foreach ( $_POST as $key => $value ) {
            // if order meta label is empty then related all array are empty.
            if ( ! array_key_exists('_winvoice_order_meta_label_ps', $_POST) ) {
                update_option( '_winvoice_order_meta_label_ps', array() );
                update_option( '_winvoice_order_meta_name_ps', array() );
                update_option( '_winvoice_order_meta_name_position_ps', array() );
            }

            if ( 'wpifw_packingslip_background_attachment_id' === $key ) {
                $full_size_path = get_attached_file( $value );
                update_option( $key, $full_size_path );
                update_option( 'wpifw_packingslip_background_attachment_image_id', $value );
            } elseif ( strpos( $key, '_winvoice_post_meta_label' ) !== false ) {
                $meta = substr( $key, 0, strpos( $key, '_winvoice_post_meta_label' ) );
                if ( '' !== $meta && '' !== $value ) {
                    $meta_array[ $meta ] = $value;
                }
            } elseif ( strpos( $key, '_winvoice_post_meta_name' ) !== false ) {
                continue;
            } elseif ( '_winvoice_order_meta_label_ps' == $key ) {
                $label_ps = array();
                $name_ps = array();
                $place_ps = array();
                $label_arr_ps = isset($_POST['_winvoice_order_meta_label_ps']) ? wp_unslash($_POST['_winvoice_order_meta_label_ps']) : array(); //phpcs:ignore
                $name_arr_ps  = isset($_POST['_winvoice_order_meta_name_ps']) ? wp_unslash($_POST['_winvoice_order_meta_name_ps']) : array(); //phpcs:ignore
                $place_arr_ps = isset($_POST['_winvoice_order_meta_name_position_ps']) ? wp_unslash($_POST['_winvoice_order_meta_name_position_ps']) : array(); //phpcs:ignore

                foreach ( $label_arr_ps as $index => $val ) {
                    if ( ! '' == $val && ! '' == $name_arr_ps[ $index ] && ! '' == $place_arr_ps[ $index ] ) {
                        array_push($label_ps, $val);
                        array_push($name_ps, $name_arr_ps[ $index ]);
                        array_push($place_ps, $place_arr_ps[ $index ]);
                    }else {
                        $label_arr_ps[ $index ] = '';
                        $name_arr_ps[ $index ] = '';
                        $place_arr_ps[ $index ] = '';
                    }
                }
                if ( count($label_ps) > 0 && count($name_ps) > 0 && count($place_ps) > 0 ) {
                    update_option( $key, $label_ps );
                    update_option( '_winvoice_order_meta_name_ps', $name_ps );
                    update_option( '_winvoice_order_meta_name_position_ps', $place_ps );
                }else {
                    update_option( '_winvoice_order_meta_label_ps', array() );
                    update_option( '_winvoice_order_meta_name_ps', array() );
                    update_option( '_winvoice_order_meta_name_position_ps', array() );
                }
            }elseif ( strpos( $key, '_winvoice_order_item_meta_label' ) !== false ) {
                $meta = substr( $key, 0, strpos( $key, '_winvoice_order_item_meta_label' ) );
                if ( '' !== $meta && '' !== $value ) {
                    $order_item_meta_array_ps[ $meta ] = $value;
                }
            } elseif ( strpos( $key, '_winvoice_order_item_meta_name_ps' ) !== false ) {
                continue;
            }  else {
                if ( '_winvoice_order_meta_name_position_ps' == $key || '_winvoice_order_meta_name_ps' == $key ) {
                }else {
                    if( is_array( $value ) ) {
                        update_option( $key, $value );
                    } else {
                        update_option( $key, sanitize_text_field( $value ) );
                    }
                }
            }
        }
        update_option( 'wpifw_custom_post_meta_ps', $meta_array );
        update_option( 'wpifw_order_item_meta_ps', $order_item_meta_array_ps );
    }
    // static file form submitted.
    if ( isset( $_POST['wpifw_submit_static_files'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }
        if ( isset( $_POST['wpifw_static_attach_check_list'] ) ) {
            $static_file_check_list = array();
            foreach ( $_POST['wpifw_static_attach_check_list'] as $key => $value ) { //phpcs:ignore
                $static_file_check_list[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
            }
            update_option( 'wpifw_static_attach_check_list', $static_file_check_list );
        } else {
            update_option( 'wpifw_static_attach_check_list', array() );
        }
        foreach ( $_POST as $key => $value ) {
            if ( 'wpifw_static_attach_check_list' !== $key ) {
                update_option( $key, sanitize_text_field( $value ) );
            }
        }
    }

    // Seller form submitted, update the option settings.
    if ( isset( $_POST['wpifw_submit_seller'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }

        foreach ( $_POST as $key => $value ) {

            if ( 'wpifw_terms_and_condition' === $key || 'wpifw_other_information' === $key ) {
                update_option( $key, stripslashes($value) );
            } elseif ( 'wpifw_cdetails' === $key ) {
                update_option( $key, sanitize_textarea_field( $value ) );
            } elseif ( 'wpifw_logo_attachment_id' === $key ) {
                $full_size_path = get_attached_file( $value );
                update_option( $key, $full_size_path );
                update_option( 'wpifw_logo_attachment_image_id', $value );
            } else {
                update_option( $key, sanitize_text_field( $value ) );
            }
        }
    }

    // Buyer form submitted, update the option settings.
    if ( isset( $_POST['wpifw_submit_buyer'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }

        foreach ( $_POST as $key => $value ) {
            if ( 'wpifw_buyer' === $key || 'wpifw_buyer_shipping_address' === $key ) {
                update_option( $key, sanitize_textarea_field( $value ) );
            } else {
                update_option( $key, sanitize_text_field( $value ) );
            }
        }
    }

    // Invoice Localization form submitted, update the option settings.
    if ( isset( $_POST['wpifw_submit_invoice_localization'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }
        foreach ( $_POST as $key => $value ) {
            update_option( $key, sanitize_text_field( $value ) );
        }
    }

    // Packing Slip Localization form submitted, update the option settings.
    if ( isset( $_POST['wpifw_submit_packingslip_localization'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }
        foreach ( $_POST as $key => $value ) {
            update_option( $key, sanitize_text_field( $value ) );
        }
    }

    // Bulk Download Form submitting checked started.
    if ( isset( $_POST['wpifw_submit_bulk_download'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }

        if ( ! isset( $_POST['wpifw_enable_compress_bulk_download'] ) ) {
            update_option( 'wpifw_enable_compress_bulk_download', '' );
        }

        foreach ( $_POST as $key => $value ) {
            if ( 'wpifw_add_fields_csv' !== $key ) {
                update_option( $key, sanitize_text_field( $value ) );
            }
        }
        // Query Orders for shipping Label.
        $args = array(
            'limit'  => - 1,
            'type'   => 'shop_order',
            'return' => 'ids',
        );
        if ( isset( $_POST['wpifw_date_from'] ) && ! empty( $_POST['wpifw_date_from'] ) && isset( $_POST['wpifw_date_to'] ) && ! empty( $_POST['wpifw_date_to'] ) ) {
            $date_from = sanitize_text_field( wp_unslash( $_POST['wpifw_date_from'] ));
            $date_to   = sanitize_text_field( wp_unslash( $_POST['wpifw_date_to'] ));

        } else {
            $date_from = strtotime( gmdate( 'Y-m-d' ) );
            $date_to   = strtotime( gmdate( 'Y-m-d' ) );
        }



        $args['date_created'] = $date_from . '...' . $date_to;

        if ( isset( $_POST['wpifw_bulk_download_order_status'] ) && ! empty( $_POST['wpifw_bulk_download_order_status'] ) && 'all' !== $_POST['wpifw_bulk_download_order_status'] ) {
            $args['status'] = sanitize_text_field( wp_unslash( $_POST['wpifw_bulk_download_order_status'] ));
        }

        if ( isset( $_POST['wpifw_bulk_download_order_email'] ) && ! empty( $_POST['wpifw_bulk_download_order_email'] ) ) {
            $args['customer'] = sanitize_text_field( wp_unslash( $_POST['wpifw_bulk_download_order_email'] ));
        }

        if ( isset( $_POST['wpifw_bulk_download_order_user_id'] ) && ! empty( $_POST['wpifw_bulk_download_order_user_id'] ) ) {
            $args['customer_id'] = sanitize_text_field( wp_unslash( $_POST['wpifw_bulk_download_order_user_id'] ));
        }

        if ( isset( $_POST['wpifw_bulk_download_order_payment_method'] ) && ! empty( $_POST['wpifw_bulk_download_order_payment_method'] ) && 'all' !== $_POST['wpifw_bulk_download_order_payment_method'] ) {
            $args['payment_method'] = sanitize_text_field( wp_unslash( $_POST['wpifw_bulk_download_order_payment_method'] ));
        }

        $order_ids = wc_get_orders( $args );
        // echo "<pre>"; print_r($order_ids);die();!
        if ( empty( $order_ids ) ) {
            $status = esc_html__( 'No order found with your given date range.', 'webappick-pdf-invoice-for-woocommerce' );
            wp_safe_redirect( add_query_arg( array( 'message' => $status ), admin_url( 'admin.php?page=webappick-woo-invoice-settings' ) ) );
            exit();
        }

        $order_ids          = implode( ',', $order_ids );
        $url                = wp_nonce_url( admin_url( 'admin-ajax.php' ), 'wpifw_pdf_nonce' );
        $param['order_ids'] = $order_ids;

        // Bulk type checked and downloads the invoice and slip between the input dates.
        if ( isset( $_POST['wpifw_bulk_type'] ) && 'WPIFW_INVOICE_DOWNLOAD' === $_POST['wpifw_bulk_type'] ) {
            $param['action'] = 'wpifw_generate_invoice_bulk';
            wp_safe_redirect( add_query_arg( $param, $url ) );
            exit;
        } elseif ( isset( $_POST['wpifw_bulk_type'] ) && 'WPIFW_PACKING_SLIP' === $_POST['wpifw_bulk_type'] ) {
            $param['action'] = 'wpifw_generate_invoice_packing_slip_bulk';
            wp_safe_redirect(add_query_arg($param, $url));
            exit;
        } elseif ( isset( $_POST['wpifw_bulk_type'] ) && 'WPIFW_CREDIT_NOTE' === $_POST['wpifw_bulk_type'] ) {
            $param['action'] = 'wpifw_generate_credit_note_bulk';
            wp_safe_redirect( add_query_arg( $param, $url ) );
            exit;
        }elseif ( isset( $_POST['wpifw_bulk_type'] ) && 'WPIFW_CSV_DOWNLOAD' === $_POST['wpifw_bulk_type'] ) {
            $param['action'] = 'wpifw_generate_csv_order_list';
            wp_safe_redirect( add_query_arg( $param, $url ) );
            exit;
        }
    }

    // Batch Shipping label Form submitting checked started.
    if ( isset( $_POST['wpifw_submit_shipping_lebel'] ) ) {
        // Verify Nonce.
        $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! wp_verify_nonce( $retrieved_nonce, 'settings_form_nonce' ) ) {
            die( 'Failed security check' );
        }

        // Sanitize Inputs.
        $shipping_label = array();
        foreach ( $_POST as $key => $value ) {
            if ( 'wpifw_shipping_lebel_buyer' === $key ) {
                $shipping_label[ $key ] = sanitize_textarea_field( $value );
            } else {
                $shipping_label[ $key ] = sanitize_text_field( $value );
            }
        }

        $args      = array(
            'date_created' => $shipping_label['wpifw_shipping_lebel_date_from'] . '...' . $shipping_label['wpifw_shipping_lebel_date_to'],
            'type'         => 'shop_order',
            'limit'        => -1,
            'status'       => 'completed',
            'return'       => 'ids',
        );


        $order_ids = wc_get_orders( $args );
        if ( empty( $order_ids ) ) {
            $status = esc_html__( 'No completed order found with your given date range.', 'webappick-pdf-invoice-for-woocommerce' );
            wp_safe_redirect( add_query_arg( array( 'shipping_label_message' => $status ), admin_url( 'admin.php?page=webappick-woo-invoice-settings' ) ) );
            exit();
        }
        $order_ids = implode( ',', $order_ids );
        $columns    = $shipping_label['wpifw_shipping_lebel_num_col'];
        $rows       = $shipping_label['wpifw_shipping_lebel_num_row'];
        $paper_size = $shipping_label['wpifw_shipping_lebel_paper'];
        $font_size  = $shipping_label['wpifw_shipping_lebel_font_size'];

        if ( 'custom' === $paper_size ) {
            $custom_width  = $shipping_label['wpifw_shipping_lebel_custom_paper_wide'];
            $custom_height = $shipping_label['wpifw_shipping_lebel_custom_paper_height'];

            $paper_size = implode( ',', array( $custom_width, $custom_height ) );
        }

        $meta_labels = array_map( 'esc_attr', $_POST['shipping_label_meta_labels'] );//phpcs:ignore
        $meta_keys   = array_map( 'esc_attr', $_POST['shipping_label_metas'] );//phpcs:ignore

        update_option( 'wpifw_shipping_lebel_buyer', $shipping_label['wpifw_shipping_lebel_buyer'] );
        update_option( 'wpifw_shipping_lebel_block_title_to', $shipping_label['wpifw_shipping_lebel_block_title_to'] );
        update_option( 'wpifw_shipping_lebel_font_size', $shipping_label['wpifw_shipping_lebel_font_size'] );
        update_option( 'wpifw_shipping_label_meta_labels', $meta_labels );
        update_option( 'wpifw_shipping_label_metas', $meta_keys );
        update_option( 'wpifw_shipping_lebel_paper', $paper_size );

        $url = wp_nonce_url( admin_url( 'admin-ajax.php' ), 'wpifw_pdf_nonce' );
        wp_safe_redirect(
            add_query_arg(
                array(
                    'action'     => 'wpifw_generate_shipping_label',
                    'paper_size' => $paper_size,
                    'column'     => $columns,
                    'row'        => $rows,
                    'order_ids'  => $order_ids,
                    'font'       => $font_size,
                ),
                $url
            )
        );
        exit;
    }


    // Load plugin settings view.
    require plugin_dir_path( __FILE__ ) . 'admin/partials/woo-invoice-pro-admin-display.php';
}

/**
 * Load plugin docs from API.
 */
function woo_invoice_pro_docs(){

    // Enter the name of your blog here followed by /wp-json/wp/v2/posts and add filters like this one that limits the result to 2 posts.
    $response = wp_remote_get( 'https://webappick.com/wp-json/wp/v2/docs/?parent=3960&_fields=parent,title,link,id' );

    // Exit if error.
    if ( is_wp_error( $response ) ) {
        return;
    }

    // Get the body.
    $posts = json_decode( wp_remote_retrieve_body( $response ) );

    // Exit if nothing is returned.
    if ( empty( $posts ) ) {
        return;
    }
    if ( ! empty( $posts ) ) {
        $new_posts = [ $posts[2], $posts[1], $posts[0], $posts[3] ];
        ?>
        <div class="_winvoice_docs">
            <?php foreach ( $new_posts as $post ) {
                $box_id = ( isset( $post->title->rendered ) ) ? sanitize_title( $post->title->rendered ) : '';
                ?>
                <div id="<?php echo esc_attr( $box_id ); ?>" class="postbox ">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text">
                            <?php printf( esc_html__( 'Toggle panel: %s', 'webappick-pdf-invoice-for-woocommerce' ), esc_html( $post->title->rendered ) ); //phpcs:ignore ?>
                        </span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle">
                        <span class="dashicons dashicons-sos" aria-hidden="true"></span>
                        <span><?php echo esc_html( $post->title->rendered ); ?></span>
                    </h2>
                    <div class="inside">
                        <div class="main">
                            <?php
                            $response1 = wp_remote_get( 'https://webappick.com/wp-json/wp/v2/docs/?per_page=60&parent='.$post->id.'&_fields=parent,title,link,id,doc_tag' );
                            $posts1 = json_decode( wp_remote_retrieve_body( $response1 ) );
                            ?>
                            <ul>
                                <?php
                                // For each post.
                                foreach ( $posts1 as $post ) {
                                    ?>
                                    <li style="padding-bottom: 20px">
                                        <span class="dashicons dashicons-media-text" aria-hidden="true"></span>

                                        <a href="<?php echo esc_url( $post->link ); ?>" style="font-size: 14px;line-height: 20px" target="_blank">
                                            <?php
                                            echo esc_html( $post->title->rendered );
                                            ?></a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div><!-- end ._winvoice_docs-->
        <?php
    }

}

/**
 * Get Invoice number by order id
 *
 * @param int $id Order Id.
 *
 * @return mixed|string
 */
function woo_invoice_pro_get_invoice_number( $id ) {

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    $order = woo_invoice_pro_get_order($id); // Get the order object
    if (!$order || !should_generate_invoice($order)) {
        return false;
    }

    // This key is deprecated from version 4.1.1 .
    // But still need for getting our plugin's generated invoice number.
    $invoice_no = woo_invoice_pro_get_meta($order, 'wpifw_invoice_no', true);

    if (empty($invoice_no)) {
        // Check for previous installed third party plugins generated invoice number.
        if ('' != woo_invoice_pro_get_meta($order, '_wcpdf_invoice_number', true)) {
            // Plugin : woocommerce-pdf-invoices-packing-slips
            $invoice_no = woo_invoice_pro_get_meta($order, '_wcpdf_invoice_number', true);
        } elseif ('' != woo_invoice_pro_get_meta($order, '_bewpi_invoice_pdf_path', true)) {
            // Plugin : woocommerce-pdf-invoices
            // This is a path need to find out the invoice number.
            $post_meta = woo_invoice_pro_get_meta($order, '_bewpi_invoice_pdf_path', true);
            preg_match('/[\/]{1}[\d\D]{1,}[.]{1}/i', $post_meta, $matches);
            $invoice_no = substr($matches[0], 1, -1);
        } elseif ('' != woo_invoice_pro_get_meta($order, 'wf_invoice_number', true)) {
            // Plugin : print-invoices-packing-slip-labels-for-woocommerce
            $invoice_no = woo_invoice_pro_get_meta($order, 'wf_invoice_number', true);
        } elseif ('' != woo_invoice_pro_get_meta($order, '_wcdn_invoice_number', true)) {
            // Plugin : woocommerce-delivery-notes
            $invoice_no = woo_invoice_pro_get_meta($order, '_wcdn_invoice_number', true);
        } elseif ('' != woo_invoice_pro_get_meta($order, '_ywpi_invoice_number', true)) {
            // Plugin : woocommerce-delivery-notes
            $invoice_no = woo_invoice_pro_get_meta($order, '_ywpi_invoice_number', true);
        } else {
            // Plugin : webappick-pdf-invoice-for-woocommerce-pro
            $invoice_no = $id;

            // Invoice Number Type.
            $get_number_type = get_option('wpifw_invoice_number_type');

            // Get Prefix.
            $prefix = get_option('wpifw_invoice_no_prefix');
            $prefix = !empty($prefix) ? $prefix : '';

            // Get Suffix.
            $suffix = get_option('wpifw_invoice_no_suffix');
            $suffix = !empty($suffix) ? $suffix : '';

            // Generate Invoice Number.
            if ('pre_custom_number_suf' === $get_number_type) {
                $invoice_no = $prefix . $id . $suffix;
            } elseif ('pre_order_number_suf' === $get_number_type) {
                $invoice_no = $prefix . $id . $suffix;
            }

            $invoice_no = woo_invoice_pro_process_date_macros($id, $invoice_no);

            // Check if WooCommerce Subscriptions plugin is active
            if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
                // WooCommerce Subscriptions is active
                woo_invoice_pro_update_meta( $order, 'wpifw_invoice_no_' . $id, $invoice_no );
            } else {
                // WooCommerce Subscriptions is not active
                woo_invoice_pro_update_meta( $id, 'wpifw_invoice_no', $invoice_no );
            }
        }
    }

    return $invoice_no;
}

/**
 * Get Credit Note number by order id
 *
 * @param int $id Order Id.
 *
 * @return mixed|string
 */
function woo_invoice_pro_get_credit_note_number( $id ) {
    $order = woo_invoice_pro_get_order($id); // Get the order object

    if ( ! $order ) {
        return false;
    }

    // Check for the generated credit note number in our plugin.
    $credit_note_no = woo_invoice_pro_get_meta($order, 'wpifw_credit_note_no', true);

    if ( empty( $credit_note_no ) ) {
        // No credit note found, so generate a new one for this order.
        $credit_note_no = $id;

        // Get the number type setting (with prefix/suffix).
        $get_number_type = get_option( 'wpifw_credit_note_number_type' );
        $prefix = get_option( 'wpifw_credit_note_no_prefix', '' );
        $suffix = get_option( 'wpifw_credit_note_no_suffix', '' );

        // Generate the credit note number based on the selected number type.
        if ( 'pre_custom_number_suf' === $get_number_type ) {
            $credit_note_no = $prefix . $id . $suffix;
        } elseif ( 'pre_order_number_suf' === $get_number_type ) {
            $credit_note_no = $prefix . $id . $suffix;
        }

        // Process macros (if applicable) in the credit note number.
        $credit_note_no = woo_invoice_pro_process_date_macros( $id, $credit_note_no );

        // Only update the meta if the order status is refunded
        if ( 'refunded' === $order->get_status() ) {
            woo_invoice_pro_update_meta( $id, 'wpifw_credit_note_no', $credit_note_no );
        }
    }

    return $credit_note_no;
}

/**
 * Process Macros for order or invoice id
 *
 * @param int    $order_id Order id.
 * @param string $order_no order no.
 *
 * @return mixed
 */
function woo_invoice_pro_process_date_macros( $order_id, $order_no ) {
    $order_created = get_the_date( 'Y-m-d', $order_id );
    if ( strpos( $order_no, '{{day}}' ) !== false ) {
        $order_no = str_replace( '{{day}}', gmdate( 'd', strtotime( $order_created ) ), $order_no );
    }
    if ( strpos( $order_no, '{{month}}' ) !== false ) {
        $order_no = str_replace( '{{month}}', gmdate( 'm', strtotime( $order_created ) ), $order_no );
    }
    if ( strpos( $order_no, '{{year}}' ) !== false ) {
        $order_no = str_replace( '{{year}}', gmdate( 'Y', strtotime( $order_created ) ), $order_no );
    }

    return $order_no;
}

/**
 * Process PDF Invoice Making Action
 */
function woo_invoice_pro_generate_invoice() {

    if ( ! is_user_logged_in() ) {
        auth_redirect();
        exit;
    }

    // Verify Nonce.
    $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
    if ( ! wp_verify_nonce( $retrieved_nonce, 'wpifw_pdf_nonce' ) ) {
        die( 'Failed security check' );
    }

    if ( isset( $_REQUEST['order_id'] ) ) {

        $ids   = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
        $order = wc_get_order( $ids );

        if ( woo_invoice_pro_pdf_can_generate($order) ) {
            WPIFW_PDF( $ids )->generatePDF( $ids );
        } else {
            die( 'You are not allowed to download this invoice.' );
        }
    } elseif ( isset( $_REQUEST['order_ids'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_ids'] ));

        if ( '' !== $order_ids ) {
            $order = wc_get_order( explode( ',', $order_ids)[0] );
        }else {
            die( 'There is no order is selected.' );
        }

        if ( woo_invoice_pro_pdf_can_generate($order) ) {

            WPIFW_PDF( $order_ids )->generatePDF( $order_ids );
        } else {
            die( 'You are not allowed to download invoices.' );
        }
    }
}

add_action( 'wp_ajax_wpifw_generate_invoice', 'woo_invoice_pro_generate_invoice' );



/**
 * Process PDF Credit Note Making Action
 */
function woo_invoice_pro_generate_credit_note() {

    if ( ! is_user_logged_in() ) {
        auth_redirect();
        exit;
    }

    // Verify Nonce.
    $retrieved_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
    if ( ! wp_verify_nonce( $retrieved_nonce, 'wpifw_pdf_nonce' ) ) {
        die( 'Failed security check' );
    }

    if ( isset( $_REQUEST['order_id'] ) ) {
        $ids   = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
        $order = wc_get_order( $ids );
        $user  = $order->get_user_id();
        $invoice_can_generate = ( null != get_option('wpifw_invoice_can_generate') ) ? get_option('wpifw_invoice_can_generate') : [];

        if ( woo_invoice_pro_pdf_can_generate($order) ) {
            WPIFW_PDF( $ids )->generatePDF( $ids );
        } else {
            die( 'You are not allowed to download this invoice.' );
        }
    }
}

add_action( 'wp_ajax_wpifw_generate_credit_note', 'woo_invoice_pro_generate_credit_note' );

/**
 * Process Bulk PDF Invoice Making Action
 */
function woo_invoice_pro_generate_invoice_bulk() {
    // Verify Nonce.
    check_ajax_referer( 'wpifw_pdf_nonce' );
    $order_ids = '';
    if ( isset( $_REQUEST['order_id'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
    } elseif ( isset( $_REQUEST['order_ids'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_ids']) );
    }

    WPIFW_PDF( $order_ids )->generatePDF( $order_ids, 'bulk', '' );
}

add_action( 'wp_ajax_wpifw_generate_invoice_bulk', 'woo_invoice_pro_generate_invoice_bulk' );

/**
 * Process Bulk PDF Credit Note Making Action
 */
function woo_invoice_pro_generate_credit_note_bulk() {
    // Verify Nonce.
    check_ajax_referer( 'wpifw_pdf_nonce' );

    $order_ids = '';
    if ( isset( $_REQUEST['order_id'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
    } elseif ( isset( $_REQUEST['order_ids'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_ids'] ) );
    }

    // Filter out non-refunded orders
    $order_ids_array = explode(',', $order_ids);
    $refunded_order_ids = [];

    foreach ( $order_ids_array as $order_id ) {
        $order = wc_get_order( $order_id );
        if ( $order && $order->has_status( 'refunded' ) ) {
            $refunded_order_ids[] = $order_id;
        }
    }

    if ( empty( $refunded_order_ids ) ) {
        die( 'No refunded orders found.' );
    }

    $refunded_order_ids_string = implode(',', $refunded_order_ids);
    WPIFW_PDF( $refunded_order_ids_string )->generatePDF( $refunded_order_ids_string, 'bulk_credit_note', '' );
}

add_action( 'wp_ajax_wpifw_generate_credit_note_bulk', 'woo_invoice_pro_generate_credit_note_bulk' );

/**
 * Process PDF Packing slip Making Action
 */
function woo_invoice_pro_generate_packing_slip() {
    // Verify Nonce.
    check_ajax_referer( 'wpifw_pdf_nonce' );
    $order_ids = '';
    if ( isset( $_REQUEST['order_id'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
    } elseif ( isset( $_REQUEST['order_ids'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_ids'] ));
    }
    $vendor = null;
    if ( isset( $_REQUEST['vendor'] ) ) {
        $vendor = sanitize_text_field( wp_unslash( $_REQUEST['vendor'] ));
    }

    WPIFW_PDF( $order_ids )->generatePackingSlip( $order_ids, null, null, $vendor );
}

add_action( 'wp_ajax_wpifw_generate_invoice_packing_slip', 'woo_invoice_pro_generate_packing_slip' );

/**
 * Process Bulk PDF Packing slip Making Action
 */
function woo_invoice_pro_generate_packing_slip_bulk() {
    // Verify Nonce.
    check_ajax_referer( 'wpifw_pdf_nonce' );
    $order_ids = '';
    if ( isset( $_REQUEST['order_id'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
    } elseif ( isset( $_REQUEST['order_ids'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_ids'] ));
    }

    WPIFW_PDF( $order_ids )->generatePackingSlip( $order_ids, 'bulk', '' );
}

add_action( 'wp_ajax_wpifw_generate_invoice_packing_slip_bulk', 'woo_invoice_pro_generate_packing_slip_bulk' );

/**
 * Process Shipping label action
 */
function woo_invoice_pro_generate_shipping_label() {
    // Verify Nonce.
    check_ajax_referer( 'wpifw_pdf_nonce' );
    $order_ids = '';
    if ( isset( $_REQUEST['order_id'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
    } elseif ( isset( $_REQUEST['order_ids'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_ids'] ));
    }


    $columns    = ( isset( $_REQUEST['column'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['column'] )) : '';
    $rows       = ( isset( $_REQUEST['row'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['row'] )) : '';
    $paper_size = ( isset( $_REQUEST['paper_size'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['paper_size'] )) : '';
    $font_size  = ( isset( $_REQUEST['font'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['font'] )) : '';


    WPIFW_PDF( $order_ids, $columns, $paper_size, $rows, $font_size )->generateShippingLabel( $order_ids );

}

add_action( 'wp_ajax_wpifw_generate_shipping_label', 'woo_invoice_pro_generate_shipping_label' );

/**
 * Process PDF Invoice Making Action
 */
function woo_invoice_pro_generate_order_list() {
    // Verify Nonce.
    check_ajax_referer( 'wpifw_pdf_nonce' );
    $order_ids = '';
    if ( isset( $_REQUEST['order_id'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
    } elseif ( isset( $_REQUEST['order_ids'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_ids'] ));
    }

    WPIFW_PDF( $order_ids )->generateOrderList();
}

add_action( 'wp_ajax_wpifw_generate_order_list', 'woo_invoice_pro_generate_order_list' );
/**
 * Process CSV Invoice Making Action
 */
function woo_invoice_pro_generate_csv_order_list() {
    // Verify Nonce.
    check_ajax_referer( 'wpifw_pdf_nonce' );
    $order_ids = '';
    if ( isset( $_REQUEST['order_id'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_id'] ));
    } elseif ( isset( $_REQUEST['order_ids'] ) ) {
        $order_ids = sanitize_text_field( wp_unslash( $_REQUEST['order_ids'] ));
    }

    WPIFW_PDF( $order_ids )->generateCSVOrderList();
}

add_action( 'wp_ajax_wpifw_generate_csv_order_list', 'woo_invoice_pro_generate_csv_order_list' );

function woo_invoice_pro_deactivate_free() {
    if ( ! function_exists( 'deactivate_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $woo_invoice_free = 'webappick-pdf-invoice-for-woocommerce/woo-invoice.php';
    if ( is_plugin_active( $woo_invoice_free ) ) {
        deactivate_plugins( $woo_invoice_free );
    }

}

/**
 * Who can generate pdf.
 */

function woo_invoice_pro_pdf_can_generate( $order ) {
    $is_invoice_generate_ignore = apply_filters('wpifw_invoice_generate_ignore', true, $order);

    if ( ! $is_invoice_generate_ignore ) {
        wp_die('Invoice can not generated');
    }

    $invoice_can_generate = ( null != get_option('wpifw_invoice_can_generate') ) ? get_option('wpifw_invoice_can_generate') : [];

    $user  = $order->get_user_id();
    $default_role = [
        'administrator',
        'shop_manager',
    ];

    $default_role = array_unique(array_merge($default_role, $invoice_can_generate));
    foreach ( $default_role as $key => $value ) {
        if ( wc_user_has_role( get_current_user_id(), $value)
            || get_current_user_id() == $user ) {
            return true;
        } else {
            $status = false;
        }
    }

    return $status;

}

/**
 *
 * Create short code for qr code.
 * Example [challan_pro_qr_code order_id="390"]
 * @param $atts
 * @return string
 */
function create_shortcode( $atts ) {

    $helper = woo_invoice_pro_helper();
    $order = wc_get_order($atts['order_id']);
    $qr_code = $helper->get_qr_code($order);

    return "<img  src='https://chart.googleapis.com/chart?cht=qr&chl=$qr_code&chs=160x160&chld=L|0'/>";//ignore:phpcs;;

}

add_shortcode( 'challan_pro_qr_code', 'create_shortcode' );
