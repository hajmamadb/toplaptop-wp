<?php
/**
 * Class Challan_Pro_Notifications
 */
class Challan_Pro_Notifications {

    private $occasion_key = 'halloween25'; // Set the occasion key (e.g., 'black_friday', 'halloween').
    private $occasion_name = 'halloween25'; // Set the occasion name.

    public function __construct() {
        $this->notifications_load_hooks();
    }

    /**
     * Load all Notifications hooks.
     */
    public function notifications_load_hooks() {
        // Only load the notice if there is a valid occasion
        if ( $this->occasion_key ) {
            //if ( isset($_GET['page'] ) && preg_match( '/^webappick([a-zA-Z-]+)/', $_GET['page'] ) ) {//phpcs:ignore
            if ( isset( $_GET['page'] ) && 'webappick-woo-invoice-settings' === $_GET['page'] ) { //phpcs:ignore
                //add_action( 'admin_notices', [ $this, 'challan_pro_deal_notice' ] );
            }
            add_action( 'wp_ajax_challan_pro_hide_notice', [ $this, 'challan_pro_hide_notice' ] );
        }
    }
    /**
     * Display the deal notification.
     */
    public function challan_pro_deal_notice() {
        $has_notice = false;

        // Check if the notice is already dismissed for the given occasion
        if ( get_option( 'challan_pro_' . $this->occasion_key ) !== 'shown_' . $this->occasion_key . '_banner' ) {
            $has_notice = true;
            $nonce = wp_create_nonce( 'challan_pro_notice_nonce' );
            $plugin_name = sprintf( '<b>%s</b>', esc_html__( 'Challan', 'webappick-pdf-invoice-for-woocommerce' ) );

            // Image URL path dynamically based on the occasion key
            $image_url = plugin_dir_url( __FILE__ ) . '../../admin/images/' . $this->occasion_key . '-lifetime-banner-25.gif';

            ?>
            <div class="_winvoice-notice notice notice-info is-dismissible" style="line-height:1.5;width: 100%" data-which="<?php echo esc_attr( $this->occasion_key ); ?>_close" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <p style="margin: 0; padding: 0;"><?php
                    printf(
                        __( '<a class="_winvoice_deal_notice" href="https://webappick.com/discount-deal/?utm_source=halloween_25&utm_medium=wp_free&utm_campaign=halloween_25" target="_blank">
<img  src="' . esc_url( $image_url ) . '" class="_winvoice-notice-image" alt="' . esc_attr( $this->occasion_name ) . '">
</a>', 'webappick-pdf-invoice-for-woocommerce' ),
                        $plugin_name
                    );
                    ?></p>
            </div>
            <style>
                /* Ensure image stays within container width */
                ._winvoice-notice-image {
                    max-width: 100%;
                    height: auto;
                    display: block;
                }
                ._winvoice-notice .notice-dismiss:before {
                    color: #fff !important;
                }

            </style>
            <?php

            if ( $has_notice ) {
                add_action( 'admin_print_footer_scripts', function() use ( $nonce ) {
                    ?>
                    <script>
                        (function($){
                            "use strict";
                            $(document)
                                .on('click', '._winvoice-notice .notice-dismiss', function (e) {
                                    e.preventDefault();
                                    var self = $(this), invoice_notice = self.closest('._winvoice-notice'), which = invoice_notice.attr('data-which');
                                    wp.ajax.post( 'challan_pro_hide_notice', { _wpnonce: '<?php echo esc_attr( $nonce ); ?>', which: which } );
                                });
                        })(jQuery)
                    </script><?php
                }, 99 );
            }
        }
    }

    /**
     * Ajax Action For Hiding the Notice
     */
    public function challan_pro_hide_notice() {
        check_ajax_referer( 'challan_pro_notice_nonce' );
        $notices = [ $this->occasion_key . '_close' ];
        if ( isset( $_REQUEST['which'] ) && ! empty( $_REQUEST['which'] ) && in_array( $_REQUEST['which'], $notices ) ) {

            if ( $_REQUEST['which'] == $this->occasion_key . '_close' ) {
                update_option( 'challan_pro_' . $this->occasion_key, 'shown_' . $this->occasion_key . '_banner' );
            }

            wp_send_json_success( true );
            wp_die();
        }
        wp_send_json_error( esc_html__( 'Invalid Request.', 'webappick-pdf-invoice-for-woocommerce' ) );
        wp_die();
    }

}

/**
 * Instantiate the notification.
 * You can change the $occasion_key and $occasion_name inside the class to switch between occasions.
 */
new Challan_Pro_Notifications();
