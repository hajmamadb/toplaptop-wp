<?php

if ( get_option( 'wpifw_pdf_invoice_upload_to_dropbox' ) == '1' ) {
	if ( file_exists( CHALLAN_PRO_INVOICE_DIR . '/dropboxapi/composer.json' ) ) {
		require_once CHALLAN_PRO_INVOICE_DIR . '/dropboxapi/vendor/autoload.php';
	}
}

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\DropboxClient;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

use Webappick\zatca\GenerateQrCode;
use Webappick\zatca\Tag;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  https://webappick.com
 * @since 1.0.0
 *
 * @package    Woo_Invoice_Pro
 * @subpackage Woo_Invoice_Pro/includes
 */

/**
 * This class responsible for maintaining and registering all hooks that power
 * the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Invoice_Pro
 * @subpackage Woo_Invoice_Pro/includes
 * @author     Md Ohidul Islam <wahid@webappick.com>
 */
class Woo_Invoice_Pro_Hooks {

	/**
	 * Woo_Invoice_Pro_Hooks constructor.
	 */
	public function __construct() {


		if ( '0' !== get_option( 'wpifw_invoicing' ) ) {

			// ####################### MY ACCOUNT ACTIONS ############################
			// Add Download Invoice button to My Account Order List Page.
			if ( ! empty( get_option( 'wpifw_download' ) ) ) {
				add_filter(
					'woocommerce_my_account_my_orders_actions',
					array(
						$this,
						'add_my_account_order_action_download_invoice',
					),
					10,
					2
				);

                add_action('wp', function() {
                    if (is_wc_endpoint_url('order-received') || is_wc_endpoint_url('view-order')) {
                        remove_filter('woocommerce_my_account_my_orders_actions', array($this, 'add_my_account_order_action_download_invoice'), 10);
                    }
                });

                // Redirect to new tab
				if ( 'new_tab' == get_option( 'wpifw_pdf_invoice_button_behaviour' ) || empty( 'new_tab' == get_option( 'wpifw_pdf_invoice_button_behaviour' ) ) ) {
					add_action( 'woocommerce_after_account_orders', array( $this, 'action_after_account_orders_js' ) );
				}
			}

			if ( ! empty( get_option( 'wpifw_download' ) ) ) {
				add_filter(
					'woocommerce_my_account_my_orders_actions',
					array(
						$this,
						'add_my_account_order_action_download_credit_note',
					),
					1,
					2
				);

                // Redirect to new tab
				if ( 'new_tab' == get_option( 'wpifw_pdf_invoice_button_behaviour' ) || empty( 'new_tab' == get_option( 'wpifw_pdf_invoice_button_behaviour' ) ) ) {
					add_action( 'woocommerce_after_account_orders', array(
						$this,
						'action_after_account_orders_credit_note_js',
					) );
				}
			}
			// Add Download Button in Order View Page.
			if ( ! empty( get_option( 'wpifw_download' ) ) ) {
				add_action(
					'woocommerce_order_details_after_order_table',
					array(
						$this,
						'add_my_account_order_view_action_download_invoice',
					)
				);
			}
			// Add Download Button for credit note in Order View Page.
			if ( ! empty( get_option( 'wpifw_download' ) ) ) {
				add_action(
					'woocommerce_order_details_after_order_table',
					array(
						$this,
						'add_my_account_order_view_action_download_credit_note',
					)
				);
			}

			// ####################### EMAIL ACTIONS ##########################################

			// Add invoice number to order.
			add_action( 'woocommerce_new_order', array( $this, 'add_invoice_number_to_order' ), 999 );
            add_action( 'woocommerce_order_status_refunded', array( $this, 'add_credit_note_number_to_order' ), 999 );
            add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_changed'), 999, 4);

            // Add QR code to email template.
			if ( get_option( 'wpifw_display_qr_code_email_template' ) ) {
				add_action( 'woocommerce_email_before_order_table', [ $this, 'add_qr_code_to_email' ], 10, 4 );

			}


			/**
			 * Added from 4.0.13
			 */
			add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id, $posted ) {
                woo_invoice_pro_update_meta( $order_id, 'woo_invoice_order_lang', get_locale() );
			}, 10, 2 );

			// Filter to add Invoice attachment with order email.
			if ( ! empty( get_option( 'wpifw_order_email' ) ) ) {
				add_filter( 'woocommerce_email_attachments', array( $this, 'attach_invoice_to_order_email' ), 90, 3 );
				// Filter to add Invoice download link with order email.
				// add_filter( 'woocommerce_email_after_order_table', array( $this, 'add_invoice_download_link' ), 91, 4 ); !
			}

            // Filter to add Packing Slip attachment to order email.
            if ( ! empty( get_option( 'wpifw_email_packing_slip' ) ) ) {
                add_filter( 'woocommerce_email_attachments', array( $this, 'attach_packing_slip_to_order_email' ), 90, 3 );
            }

			// ####################### ADMIN ORDER EDIT PAGE META BOX ############################
			// Add Custom MetaBox for PDF Download Button.
			add_action( 'add_meta_boxes', array( $this, 'add_custom_meta_box' ) );

			// ####################### ADMIN ORDER LIST TABLE ACTIONS ############################
			// Register Admin order list table bulk actions.
			add_filter( 'bulk_actions-edit-shop_order', array( $this, 'register_admin_order_list_bulk_actions' ), 11 );
			add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'register_admin_order_list_bulk_actions' ), 11 );
			// Handle bulk Invoice Making action.
			add_filter( 'handle_bulk_actions-edit-post', array( $this, 'invoice_bulk_action_handler' ), 10, 3 );
			add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'invoice_bulk_action_handler' ), 10, 3 );
			// Add admin Order list actions buttons.
			add_action(
				'woocommerce_admin_order_actions_end',
				array(
					$this,
					'register_admin_order_list_actions_buttons',
				)
			);

			// Logo selector modal.
			add_action( 'admin_footer', array( $this, 'logo_selector_print_scripts' ) );

			// ###########################################################
			// #################### VAT & SSN Field Actions ##############
			// ###########################################################
			// My Account Profile Fields.
			add_action(
				'woocommerce_edit_account_form',
				array(
					$this,
					'woo_invoice_pro_add_vat_ssn_to_edit_account_form',
				)
			);
			add_action( 'woocommerce_save_account_details', array( $this, 'woo_invoice_pro_save_vat_ssn_account_details' ), 12, 1 );
			// Admin User Edit Page.
			add_action( 'show_user_profile', array( $this, 'woo_invoice_pro_wp_vat_ssn_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'woo_invoice_pro_wp_vat_ssn_fields' ) );
			// User Edit Page.
			add_action( 'personal_options_update', array( $this, 'woo_invoice_pro_wp_vat_ssn_fields_save' ) );
			add_action( 'edit_user_profile_update', array( $this, 'woo_invoice_pro_wp_vat_ssn_fields_save' ) );
			// Checkout Fields.
			add_filter( 'woocommerce_billing_fields', array( $this, 'woo_invoice_pro_add_checkout_field' ) );
			add_action(
				'woocommerce_checkout_update_order_meta',
				array(
					$this,
					'woo_invoice_pro_checkout_field_update_order_meta',
				)
			);

			// ###########################################################
			// #################### PLUGIN SETTINGS Actions ##############
			// ###########################################################
			// Get Product Show Status.
			add_action(
				'wp_ajax_wpifw_get_product_dimension_show',
				array(
					$this,
					'woo_invoice_pro_get_product_dimension_show',
				)
			);
			// Enable Product Dimension Show.
			add_action(
				'wp_ajax_wpifw_invoice_product_dimension_show',
				array(
					$this,
					'woo_invoice_pro_product_dimension_show',
				)
			);
			// Get Product Attribute Show Status.
			add_action(
				'wp_ajax_wpifw_get_product_attribute_show',
				array(
					$this,
					'woo_invoice_pro_get_product_attribute_show',
				)
			);

			// Get Product Attribute Show Status.
			add_action(
				'wp_ajax_wpifw_get_product_column_show',
				array(
					$this,
					'woo_invoice_pro_get_product_column',
				)
			);
			// Enable Product Attribute Show.
			add_action(
				'wp_ajax_wpifw_product_attribute_show',
				array(
					$this,
					'woo_invoice_pro_product_attribute_show',
				)
			);

			// Selcet Product column.
			add_action(
				'wp_ajax_wpifw_select_product_column',
				array(
					$this,
					'wpifw_select_product_column',
				)
			);

			// Get Product column for packing slip.
			add_action(
				'wp_ajax_get_wpifw_packingslip_product_table_header',
				array(
					$this,
					'get_wpifw_packingslip_product_table_header',
				)
			);

			// Selcet Product column for packing slip.
			add_action(
				'wp_ajax_wpifw_packingslip_product_table_header',
				array(
					$this,
					'wpifw_packingslip_product_table_header',
				)
			);

			// Save csv fields.
			add_action(
				'wp_ajax_wpifw_save_csv_fields',
				array(
					$this,
					'woo_invoice_pro_wpifw_save_csv_fields',
				)
			);

			// Get bulk download csv fields.
			add_action(
				'wp_ajax_wpifw_get_csv_fields_show',
				array(
					$this,
					'woo_invoice_pro_get_csv_fields_show',
				)
			);

			// Get paid stamp show status.
			add_action( 'wp_ajax_wpifw_paid_stamp_enabled', array( $this, 'woo_invoice_pro_paid_stamp_enabled' ) );
			// Enable paid stamp show.
			add_action( 'wp_ajax_wpifw_save_paid_stamp', array( $this, 'woo_invoice_pro_save_paid_stamp' ) );
			// Save Invoice Template.
			add_action( 'wp_ajax_wpifw_save_pdf_template', array( $this, 'woo_invoice_pro_save_pdf_template' ) );

			// ################## PLUGIN HACKS ######################
			add_action( 'wp_ajax_wpifw_save_review_notice', array( $this, 'woo_invoice_pro_save_review_notice' ) );
			add_filter(
				'plugin_action_links_' . plugin_basename( __FILE__ ),
				array(
					$this,
					'woo_invoice_pro_plugin_action_links',
				)
			);

			// ################## WCFM Marketplace Hooks ######################

			if ( class_exists( 'WCFM' ) ) {
				add_filter(
					'wcfm_orders_module_actions',
					array(
						$this,
						'wcfm_orders_module_actions_callback',
					),
					10,
					3
				);
			}
			// ################## WCFM Marketplace Hooks  End ######################


			// ################## Dropbox Api ######################

			// Download Custom Fonts.
			add_action( 'wp_ajax_woo_invoice_font_download_ajax', array( $this, 'woo_invoice_font_download_ajax' ) );

			// Check DropBox API is exist.
			add_action( 'wp_ajax_woo_invoice_dropboxapi_callback', array( $this, 'woo_invoice_dropboxapi_callback' ) );

			// Download DropBox API.
			add_action( 'wp_ajax_woo_invoice_dropboxapi_download', array( $this, 'woo_invoice_dropboxapi_download' ) );

			// If one order is completed, upload previous order as pdf to dropbox.
			add_action( 'woocommerce_order_status_completed', array(
				$this,
				'woo_invoice_upload_completed_order_to_dropbox',
			), 101, 1 );

			// On input check dropbox folder exist or not.
			add_action( 'wp_ajax_woo_invoice_check_dropbox_folder_exist', array(
				$this,
				'woo_invoice_check_dropbox_folder_exist',
			) );
			// ################## Dropbox Api End ######################

			// ################## Billing Address ######################
			add_filter( 'woo_invoice_billing_info', array( $this, 'woo_invoice_billing_info_callback' ), 20, 3 );


			// ################## After order note   ######################
			if ( is_plugin_active( 'seed-confirm-pro/seed-confirm-pro.php' ) ) {
				add_action( 'woo_invoice_after_customer_notes', array(
					$this,
					'woo_invoice_add_slip_to_order_note',
				), 99, 2 );
			}

			if ( is_plugin_active( 'wc-confirm-payment/woocommerce-confirm-payment.php' ) ) {
				add_action( 'woo_invoice_after_customer_notes', array(
					$this,
					'woo_invoice_add_payment_slip_to_order_note',
				), 99, 2 );
			}

			if ( get_option('wpifw_invoice_type') === 'gstin' ) {
				add_action( 'woocommerce_product_options_general_product_data', array(
					$this,
					'add_hsn_sac_field_to_product_general_tab'
				) );
				add_action( 'woocommerce_process_product_meta', array( $this, 'save_hsn_sac_field_data' ) );
			}
		}
		// response to is document printed.
		add_action( 'wp_ajax_challan_pro_is_document_printed', [ $this, 'challan_pro_is_document_printed' ] );
		// add script to footer for configuring about is document printed or not
		add_action( 'admin_print_footer_scripts', function () {
			?>
            <script type="text/javascript">
                function challan_pro_save_is_document_printed(...parems) {
                    let arr = parems[0].split(',')
                    let buttonClass = arr[3] // button class
                    let order_id = arr[2] // order id
                    let url = arr[1] // url
                    let nonce = arr[0] // nonce
                    let buttonBehaviour = arr[4] // button behaviour example new_tab/download.
                    which = buttonClass;
                    let button = document.getElementById(buttonClass)
                    button.addEventListener('click', function (e) {
                        e.preventDefault();
                        wp.ajax.post('challan_pro_is_document_printed', {
                            _wpnonce: nonce,
                            which: which,
                            order_id: parseInt(order_id),
                        });
                    })
                    button.click();
                    url = url.replace(/&amp;/g, '&');
                    if (buttonBehaviour == 'new_tab') {
                        window.open(url, 'blank')
                    } else {
                        window.open(url)
                    }
                }

            </script><?php
		}, 99 );

	}

	/**
	 * Callback for download custom fonts
	 *
	 * @return string
	 */
	public function woo_invoice_font_download_ajax() {
		// Check valid request form user.
		check_ajax_referer( 'wpifw_pdf_nonce' );

		$get_font_content = file_get_contents( $_FILES["file"]["tmp_name"] ); // phpcs:ignore
		$file_name        = sanitize_text_field( wp_unslash( $_FILES["file"]["name"] ) ); // phpcs:ignore
		if ( ! empty( $get_font_content ) ) {
			// Make lowercase uploaded file name.
			$get_name_lowercase = strtolower( $file_name );
			// Get last extension of file name.
			$get_last_extension = strrchr( $get_name_lowercase, '.' );
			// Check last name if zip.
			if ( '.zip' !== $get_last_extension ) {
				file_put_contents( CHALLAN_PRO_FONT_DIR . $file_name, $get_font_content );
				$key = strtolower( $file_name );

				// Initialize variables before using them.
				$regular      = $file_name;
				$bold         = $file_name;
				$italic       = $file_name;
				$bold_italic  = $file_name;

				// Update variables based on conditions.
				if ( strpos( $file_name, 'regular' ) ) {
					$regular = $file_name;
				} else if ( strpos( $file_name, 'Bold' ) ) {
					$bold = $file_name;
				} else if ( strpos( $file_name, 'Italic' ) ) {
					$italic = $file_name;
				} else if ( strpos( $file_name, 'BoldItalic' ) ) {
					$bold_italic = $file_name;
				}

				$custom_font_list = array(
					$key => array(
						'R'  => $regular,
						'B'  => $bold,
						'I'  => $italic,
						'BI' => $bold_italic,
					),
				);

				$old_value = get_option( 'wpifw_custom_font_list' ) != '' ? get_option( 'wpifw_custom_font_list' ) : [];
				$data      = array_merge( $old_value, $custom_font_list );

				update_option( 'wpifw_custom_font_list', $data );

				$response = array(
					'font_name' => $file_name,
				);
				wp_send_json_success( $response );
				wp_die();
			} else {
				// Upload zip file.
				file_put_contents( CHALLAN_PRO_FONT_DIR . $file_name, $get_font_content );
				// Extract Zip file.
				if ( class_exists( 'ZipArchive' ) ) {
					$zip = new ZipArchive();
					if ( $zip->open( CHALLAN_PRO_FONT_DIR . $file_name ) === true ) {
						$zip->extractTo( CHALLAN_PRO_FONT_DIR );
						$zip->close();
						unlink( CHALLAN_PRO_FONT_DIR . $file_name );
						$response = array(
							'font_name' => $file_name,
						);
						wp_send_json_success( $response );
						wp_die();
					}
				} else {
					wp_send_json_error( 'Please enable ZipArchive php extension to extract zip file.' );
					wp_die();
				}
			}
		}
		wp_send_json_success();
		wp_die();
	}

	/**
	 * Check if dropboxapi folder is downloaded.
	 */
	public function woo_invoice_dropboxapi_callback() {
		// DropBox SDK path to save.
		$file_dir = CHALLAN_PRO_INVOICE_DIR . '/dropboxapi/';

		$count_size = 0;

		$count = 0;
		// Check DropBox SDK is already exists or not. if exist then count size.
		if ( file_exists( $file_dir . 'composer.json' ) ) {
			$dir_array = scandir( $file_dir );
			foreach ( $dir_array as $key => $filename ) {
				if ( ( '..' != $filename ) && ( '.' != $filename ) ) {
					if ( is_dir( $file_dir . '/' . $filename ) ) {
						$new_foldersize = filesize( $file_dir . '/' . $filename );
						$count_size     = $count_size + $new_foldersize;
					} elseif ( is_file( $file_dir . '/' . $filename ) ) {
						$count_size = $count_size + filesize( $file_dir . '/' . $filename );
						$count ++;
					}
				}
			}
			if ( $count_size > 0 ) {
				wp_send_json_success( $count_size );
				wp_die();
			}
		}

		wp_send_json_error();
		wp_die();

	}


	/**
	 * Callback for download DropBox API
	 * @see  https://github.com/kunalvarma05/dropbox-php-sdk
	 */
	public function woo_invoice_dropboxapi_download() {
		// DropBox SDK URL.
		$drop_box_url = esc_url( 'https://github.com/webappickhasan/invoice/raw/main/dropboxapi.zip' );

		// Get SDK name from url
		$api = basename( $drop_box_url );

		// Save api to defined directory
		$get_font_content = file_get_contents( $drop_box_url );
		if ( ! empty( $get_font_content ) ) {
			$start_time = microtime( true );
			file_put_contents( CHALLAN_PRO_INVOICE_DIR . $api, $get_font_content );
			$end_time      = microtime( true );
			$download_time = $end_time - $start_time;
			$zip           = new ZipArchive();
			if ( $zip->open( CHALLAN_PRO_INVOICE_DIR . $api ) === true ) {
				$zip->extractTo( CHALLAN_PRO_INVOICE_DIR );
				$zip->close();
				unlink( CHALLAN_PRO_INVOICE_DIR . $api );
				$response = array(
					'time'        => $download_time,
					'folder_name' => $api,
				);
				wp_send_json_success( $response );
				wp_die();

			}
		}
		wp_send_json_error();
		wp_die();
	}

	/**
	 * Upload completed order to dropbox.
	 *
	 * @param $order_id .
	 *
	 * @see  https://github.com/kunalvarma05/dropbox-php-sdk
	 */
	public function woo_invoice_upload_completed_order_to_dropbox( $order_id ) {

		if ( '' != $order_id ) {
			// Enable Dropbox api to upload pdf.
			if ( '1' == get_option( 'wpifw_pdf_invoice_upload_to_dropbox' ) && file_exists( CHALLAN_PRO_INVOICE_DIR . '/dropboxapi/composer.json' ) ) {

				$client_id     = get_option( 'wpifw_invoice_dropboxapi_client_id' );
				$client_secret = get_option( 'wpifw_invoice_dropboxapi_client_secret' );
				$access_token  = get_option( 'wpifw_invoice_dropboxapi_access_token' );
				$dropbox_dir   = get_option( 'wpifw_invoice_dropboxapi_folder_path' );

				// Check DropBox credentials data is given.
				if ( ( isset( $client_id ) && ( '' != $client_id ) ) && ( isset( $client_secret ) && ( '' != $client_secret ) ) && ( isset( $access_token ) && ( '' != $access_token ) ) ) {
					// Custom folder.
					if ( '' != $dropbox_dir ) {
						$this->upload_invoice_to_dropbox( $client_id, $client_secret, $access_token, '', $dropbox_dir );
					} else {
						$this->upload_invoice_to_dropbox( $client_id, $client_secret, $access_token );
					}
				}
			}
		}

	}

	/**
	 * Verify dropbox credentials and push completed order to dropbox.
	 *
	 * @param $client_id
	 * @param $client_secret
	 * @param $access_token
	 * @param string $local_dir
	 * @param string $dropbox_dir
	 */

	public function upload_invoice_to_dropbox( $client_id, $client_secret, $access_token, $local_dir = '', $dropbox_dir = '' ) {

		/**
		 * Configure Dropbox Application
		 *
		 * @param string $client_id Application Client ID
		 * @param string $client_secret Application Client Secret
		 * @param string $access_token Access Token
		 */
		$app = new DropboxApp( $client_id, $client_secret, $access_token );

		/**
		 * Configure Dropbox service.
		 *
		 * @param \Kunnu\Dropbox\DropboxApp
		 * @param array $config Configuration Array
		 */
		$dropbox = new Dropbox( $app );
		// Get all file from local directory.
		if ( '' != $local_dir ) {
			$file_dir = CHALLAN_PRO_INVOICE_DIR . $local_dir;
		} else {
			$file_dir = CHALLAN_PRO_INVOICE_DIR;
		}

		$files = array();
		$dir   = dir( $file_dir );
		while ( $file = $dir->read() ) {
			if ( ( '.' != $file ) && ( '..' != $file ) ) {
				$ext = explode( '.', $file );
				if ( end( $ext ) === 'pdf' ) {
					$files[] = $file;
				}
			}
		}
		$dir->close();

		try {
			foreach ( $files as $file ) {
				$file_path = CHALLAN_PRO_INVOICE_DIR . $local_dir . $file;
				if ( '' != $local_dir ) {
					$file_path = CHALLAN_PRO_INVOICE_DIR . $local_dir . $file;
				} else {
					$file_path = CHALLAN_PRO_INVOICE_DIR . $file;
				}
				// File to Upload
				$file_name = basename( $file_path );
				/**
				 * Create Dropbox File from Path
				 *
				 * @param string $file_path Path of the file to upload
				 * @param string $mode The type of access
				 */
				$dropbox_file = new DropboxFile( $file_path );
				// Upload the file to Dropbox
				if ( '' != $dropbox_dir ) {

					try {

						$dropbox->createFolder( '/' . $dropbox_dir );

						$uploaded_file = $dropbox->upload( $dropbox_file, '/' . $dropbox_dir . '/' . $file_name, array( 'autorename' => false ) );

					} catch ( Exception $e ) {
						// upload pdf to given folder.
						$uploaded_file = $dropbox->upload( $dropbox_file, '/' . $dropbox_dir . '/' . $file_name, array( 'autorename' => false ) );

					}
				} else {
					// upload pdf to dropbox main directory.
					$uploaded_file = $dropbox->upload( $dropbox_file, '/' . $file_name, array( 'autorename' => false ) );
				}
			}
		} catch ( DropboxClientException $e ) {

			echo $e->getMessage(); //phpcs:ignore

		}
	}

	/**
	 * @return void
	 * @throws DropboxClientException
	 */
	public function woo_invoice_check_dropbox_folder_exist() {
		if ( '1' == get_option( 'wpifw_pdf_invoice_upload_to_dropbox' ) ) {

			$client_id     = get_option( 'wpifw_invoice_dropboxapi_client_id' );
			$client_secret = get_option( 'wpifw_invoice_dropboxapi_client_secret' );
			$access_token  = get_option( 'wpifw_invoice_dropboxapi_access_token' );
			$app           = new DropboxApp( $client_id, $client_secret, $access_token );
			$dropbox       = new Dropbox( $app );
			try {
				// Check nonce validation
				check_ajax_referer( 'wpif_drobox_api_nonce' );

				$folder_name = isset( $_POST['fName'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['fName'] ) ), '/' ) : '';

				$dropbox->getMetadata( '/' . $folder_name );
				wp_send_json_success();
				wp_die();
			} catch ( Exception $e ) {
				wp_send_json_error();
				wp_die();
			}
		}
	}

	/**
	 * @param $actions
	 * @param $order_id
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	public function wcfm_orders_module_actions_callback( $actions, $order_id, $order ) {
		$order_id    = $order->get_id(); //phpcs:ignore
		$user        = get_current_user_id();
		$url         = wp_nonce_url( admin_url( 'admin-ajax.php?action=wpifw_generate_invoice_packing_slip&order_id=' . $order_id . '&vendor=' . $user ), 'wpifw_pdf_nonce' );
		$text        = __( 'Packing Slip', 'webappick-pdf-invoice-for-woocommerce' );
		$class       = 'tips parcial wpifw_invoice_packing_slip wpifw_button_invoice_packing_slip';
		$src         = CHALLAN_PRO_PLUGIN_URL . 'admin/images/shipping_list.svg';
		$image_class = 'width: 19px;margin-top: 4px;margin-left: 3px;';

		$actions .= '<a style="margin-bottom:-8px" target="_blank" href="' . esc_url( $url ) . '" class="wcfm-action-icon ' . esc_html( $class ) . '" data-tip="' . esc_html( $text ) . '" title="' . esc_html( $text ) . '"> <img src="' . esc_url( $src ) . '" style="' . esc_html( $image_class ) . '"/></a>';

		return $actions;

	}

    /**
     * Add Download Invoice button into My Account Order Actions for Customer
     *
     * @param array $actions My Account Order List table actions.
     * @param WC_Order $order Order Object.
     *
     * @return mixed
     */
    public function add_my_account_order_action_download_invoice( $actions, $order ) {
        $order_id = $order->get_id();
        $wpifw_invoice_download_check_list = ( get_option( 'wpifw_invoice_download_check_list' ) == false || is_null( get_option( 'wpifw_invoice_download_check_list' ) ) ) ? array() : get_option( 'wpifw_invoice_download_check_list' );
        $output_type = ( get_option( 'wpifw_output_type_html' ) ) ? '&output=html' : '';

        if ( in_array( 'always_allow', $wpifw_invoice_download_check_list ) ||
            is_current_status_checked( $order->get_status(), $wpifw_invoice_download_check_list ) ||
            ( $order->is_paid() && in_array( 'payment_complete', $wpifw_invoice_download_check_list ) )
        ) {
            if ( $order->get_customer_id() ) {
                // Download Invoice Button
                $download_invoice_text = get_option( 'wpifw_DOWNLOAD_INVOICE_TEXT' );
                $download_invoice_text = ( $download_invoice_text ) ? $download_invoice_text : 'Invoice';
                $download_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wpifw_generate_invoice&order_id=' . $order_id . $output_type ), 'wpifw_pdf_nonce' );

                // Add the custom button
                $actions['download_invoice'] = array(
                    'url'  => $download_url,
                    'name' => esc_attr( $download_invoice_text ),
                    'class' => 'wpifw-my-account-invoice',
                );
            }
        }

        // Reorder the actions array to move the custom button after the "view" button
        if ( isset( $actions['view'] ) && isset( $actions['download_invoice'] ) ) {
            $view_action = $actions['view']; // Save the "view" action
            unset( $actions['view'] ); // Remove "view" temporarily

            $actions = array_merge( [ 'view' => $view_action ], $actions ); // Add "view" back to the start
        }

        return $actions;
    }

    /**
	 * Add Download Invoice button into My Account Order Actions for Customer
	 *
	 * @param array $actions My Account Order List table actions.
	 * @param WC_Order $order Order Object.
	 *
	 * @return mixed
	 */
	public function add_my_account_order_action_download_credit_note( $actions, $order ) {

		$order_id                          = $order->get_id();
		$wpifw_invoice_download_check_list = ( get_option( 'wpifw_invoice_download_check_list' ) == false || is_null( get_option( 'wpifw_invoice_download_check_list' ) ) ) ? array() : get_option( 'wpifw_invoice_download_check_list' );

		$output_type = ( get_option( 'wpifw_output_type_html' ) ) ? '&output=html' : '';
		if ( $order->get_total_refunded() > 0 || 'refunded' === $order->get_status() ) {
            if ( $order->get_customer_id() ) {
                // Download Credit Note Button
                $url_download = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_credit_note&order_id={$order_id}&template=credit_note{$output_type}" ), 'wpifw_pdf_nonce' );

                ?>
                <a class="woocommerce-button button wpifw-my-account-credit-note" href="<?php echo esc_url( $url_download ); ?>" target="_blank" style="margin-bottom: 10px;">
                    <span class="dashicons dashicons-download"></span><?php echo esc_attr__( 'Credit Note', 'webappick-pdf-invoice-for-woocommerce' ); ?>
                </a>
                <?php
            }
		}

		return $actions;
	}

	/**
	 * Js load after order action.
	 * only for invoice;
	 */
	public function action_after_account_orders_js() {
		$action_slug = 'wpifw-my-account-invoice';
		?>
        <script>
            jQuery(function ($) {
                $('a.<?php echo esc_html( $action_slug ); ?>').each(function () {
                    $(this).attr('target', '_blank');
                })
            });
        </script>
		<?php
	}

	/**
	 * Js load after order action.
	 * only for credit note;
	 */
	public function action_after_account_orders_credit_note_js() {
		$action_slug = 'wpifw-my-account-credit-note';
		?>
        <script>
            jQuery(function ($) {
                $('a.<?php echo esc_html( $action_slug ); ?>').each(function () {
                    $(this).attr('target', '_blank');
                })
            });
        </script>
		<?php
	}

	/**
	 * JavaScript code for printing.
	 */
	private function printButtonScript( $url ) {
		?>
        <script>
            /**
             * Initiate printing using a hidden iframe.
             *
             * @param {string} url - The URL to be printed.
             */
            function printButton(url) {
                // Create a hidden iframe
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                document.body.appendChild(iframe);

                // Set the iframe source to the URL
                iframe.src = url;

                // When the iframe is loaded, initiate the print
                iframe.onload = function () {
                    iframe.contentWindow.print();
                };
            }

            /**
             * Toggle the visibility of the print options dropdown.
             *
             * @param {HTMLElement} button - The button triggering the action.
             */
            function togglePrintOptionsDropdown(button) {
                const dropdown = button.nextElementSibling;
                if (dropdown.style.display === 'none') {
                    closePrintOptionsDropdowns();
                    dropdown.style.display = 'block';
                } else {
                    dropdown.style.display = 'none';
                }
            }

            /**
             * Close all print options dropdowns.
             */
            function closePrintOptionsDropdowns() {
                const dropdowns = document.querySelectorAll('.wpifw-print-option');
                dropdowns.forEach(function(dropdown) {
                    dropdown.style.display = 'none';
                });
            }
        </script>
		<?php
	}


	/**
	 * Add download invoice button in order view actions.
	 *
	 * @param WC_Order $order Order Object.
	 */
    public function add_my_account_order_view_action_download_invoice( $order ) {
        $order_id = $order->get_id();
        $enable_print = get_option( 'wpifw_enable_print_option', '1' ); // Check admin option

        // Existing conditions to display invoice buttons
        $wpifw_invoice_download_check_list = ( get_option( 'wpifw_invoice_download_check_list' ) == false || is_null( get_option( 'wpifw_invoice_download_check_list' ) ) ) ? array() : get_option( 'wpifw_invoice_download_check_list' );
        $output_type = ( get_option( 'wpifw_output_type_html' ) ) ? '&output=html' : '';

        if (
            in_array( 'always_allow', $wpifw_invoice_download_check_list ) ||
            is_current_status_checked( $order->get_status(), $wpifw_invoice_download_check_list ) ||
            ( $order->is_paid() && in_array( 'payment_complete', $wpifw_invoice_download_check_list ) )
        ) {
            // Check if the filter allows non-logged-in users to see the button
            $allow_non_logged_in_users = apply_filters('wpifw_allow_non_logged_in_users', false);

            // Show the button if the user is logged in or if the filter allows non-logged-in users
            if ( $order->get_customer_id() || $allow_non_logged_in_users ) {
                $download_invoice_text = get_option( 'wpifw_DOWNLOAD_INVOICE_TEXT', 'Download Invoice' );
                $download_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wpifw_generate_invoice&order_id=' . $order_id . $output_type ), 'wpifw_pdf_nonce' );

                echo '<a class="woocommerce-button button wpifw-my-account-invoice" href="' . esc_url( $download_url ) . '" target="_blank" style="margin-bottom: 10px;">
                <span class="dashicons dashicons-download"></span>' . esc_attr( $download_invoice_text ) . '
            </a>';

                // Print button only if enabled in admin settings
                if ( $enable_print ) {
                    $print_invoice_text = get_option( 'wpifw_PRINT_INVOICE_TEXT', 'Print Invoice' );
                    $print_url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wpifw_generate_invoice&order_id=' . $order_id . '&print=true' . $output_type ), 'wpifw_pdf_nonce' );

                    $this->printButtonScript( $print_url );

                    echo '<a class="woocommerce-button button wpifw-my-account-invoice" onclick="printButton(\'' . esc_js( $print_url ) . '\')" style="margin-bottom: 10px;">
                    <span class="dashicons dashicons-printer"></span>' . esc_attr( $print_invoice_text ) . '
                </a>';
                }

                // Add inline styles if the filter allows it
                if ( apply_filters( 'wpifw_show_invoice_button_styles', false ) ) {
                    ?>
                    <style>
                        .wpifw-my-account-invoice, .wpifw-my-account-credit-note {
                            background-color: #2c7be5 !important;
                            border: none !important;
                            color: white !important;
                            padding: 3px 10px !important;
                            font-size: inherit !important;
                            cursor: pointer !important;
                            text-decoration: none !important;
                            display: inline-block !important;
                            margin: 10px 5px !important;
                            border-radius: 0.375em !important;
                            line-height: 25px !important;
                            width: 120px;
                            text-align: center;
                        }
                        .wpifw-my-account-invoice:hover, .wpifw-my-account-credit-note:hover {
                            background-color: #1a68d1 !important;
                        }
                        .wpifw-my-account-invoice span.dashicons, .wpifw-my-account-credit-note span.dashicons {
                            margin: 3px 2px !important;
                        }
                    </style>
                    <?php
                }
            }
        }
    }

    /**
     * Add Download Button for credit note in Order View Page.
     *
     * @param WC_Order $order Order Object.
     */
    public function add_my_account_order_view_action_download_credit_note( $order ) {
        $order_id = $order->get_id();
        $enable_print = get_option( 'wpifw_enable_print_option', '1' ); // Check admin option
        $wpifw_invoice_download_check_list = ( get_option( 'wpifw_invoice_download_check_list' ) == false || is_null( get_option( 'wpifw_invoice_download_check_list' ) ) ) ? array() : get_option( 'wpifw_invoice_download_check_list' );
        $output_type = ( get_option( 'wpifw_output_type_html' ) ) ? '&output=html' : '';

        if (
            ( 'refunded' === $order->get_status() && in_array( 'refunded', $wpifw_invoice_download_check_list ) ) ||
            in_array( 'always_allow', $wpifw_invoice_download_check_list ) ||
            $order->get_total_refunded() > 0
        ) {
            // Check if the filter allows non-logged-in users to see the button
            $allow_non_logged_in_users = apply_filters('wpifw_allow_non_logged_in_users', false);

            // Show the button if the user is logged in or if the filter allows non-logged-in users
            if ( $order->get_customer_id() || $allow_non_logged_in_users ) {
                // Download Credit Note Button
                $url_download = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_credit_note&order_id={$order_id}&template=credit_note{$output_type}" ), 'wpifw_pdf_nonce' );

                echo '<a class="woocommerce-button button wpifw-my-account-credit-note" href="' . esc_url( $url_download ) . '" target="_blank" style="margin-bottom: 10px;">
                <span class="dashicons dashicons-download"></span>' . esc_attr__( 'Download Credit Note', 'webappick-pdf-invoice-for-woocommerce' ) . '
            </a>';

                // Print button only if enabled in admin settings
                if ( $enable_print ) {
                    $url_print = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_credit_note&order_id={$order_id}&template=credit_note{$output_type}&print=true" ), 'wpifw_pdf_nonce' );

                    $this->printButtonScript( $url_print );

                    echo '<a class="woocommerce-button button wpifw-my-account-credit-note" onclick="printButton(\'' . esc_js( $url_print ) . '\')" style="margin-bottom: 10px;">
                    <span class="dashicons dashicons-printer"></span>' . esc_attr__( 'Print Credit Note', 'webappick-pdf-invoice-for-woocommerce' ) . '
                </a>';
                }
            }
        }
    }

    /**
	 * Register MetaBox to add PDF Download Button
	 *
	 * @source https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book
	 */
	public function add_custom_meta_box() {
		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';
		add_meta_box(
			'wpifw-meta-box',
			__( 'Challan Invoice', 'webappick-pdf-invoice-for-woocommerce' ),
			array(
				$this,
				'pdf_meta_box_markup',
			),
			$screen,
			'side',
			'high',
			null
		);
	}

	/**
	 * Add PDF Download button to MetaBox &
	 * Add PDF Packing Slip button to Meta Box
	 *
	 * @param object $order Order object.
	 */
	public function pdf_meta_box_markup( $order ) {
		wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );
        if ( woo_invoice_pro_hpos_enabled() ) {
			// HPOS usage is enabled.
			$order_id          = $order->get_id();
			$paper_size        = get_option( 'wpifw_shipping_lebel_paper', 'A4' );
			$font_size         = get_option( 'wpifw_shipping_lebel_font_size', '12' );
			$invoice           = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_invoice&order_id={$order_id}" ), 'wpifw_pdf_nonce' );
			$packing_slip      = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_invoice_packing_slip&order_id={$order_id}" ), 'wpifw_pdf_nonce' );
			$shipping_label    = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_shipping_label&order_id={$order_id}&paper_size={$paper_size}&font={$font_size}" ), 'wpifw_pdf_nonce' );
			$credit_note       = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_credit_note&template=credit_note&order_id={$order_id}&paper_size={$paper_size}&font={$font_size}" ), 'wpifw_pdf_nonce' );
		} else {
            // If HPOS is disabled.
			global $post;
			$paper_size        = get_option( 'wpifw_shipping_lebel_paper', 'A4' );
			$font_size         = get_option( 'wpifw_shipping_lebel_font_size', '12' );
			$invoice           = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_invoice&order_id={$post->ID}" ), 'wpifw_pdf_nonce' );
			$packing_slip      = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_invoice_packing_slip&order_id={$post->ID}" ), 'wpifw_pdf_nonce' );
			$shipping_label    = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_shipping_label&order_id={$post->ID}&paper_size={$paper_size}&font={$font_size}" ), 'wpifw_pdf_nonce' );
			$credit_note       = wp_nonce_url( admin_url( "admin-ajax.php?action=wpifw_generate_credit_note&template=credit_note&order_id={$post->ID}&paper_size={$paper_size}&font={$font_size}" ), 'wpifw_pdf_nonce' );
		}
		$invoice_text        = get_option( 'wpifw_INVOICE_TITLE', 'Invoice' );
		$packing_slip_text   = get_option( 'wpifw_PACKING_SLIP_TEXT', 'Packing Slip' );
		$shipping_label_text = get_option( 'wpifw_SHIPPING_LABEL_TEXT', 'Shipping Label' );
		?>

        <div class="wpifw_invoice_info">
			<?php $buttons = array(
				'Invoice' => $invoice,
				'Packing Slip' => $packing_slip,
				'Credit Note' => $credit_note,
				'Shipping Label' => $shipping_label
			); ?>

            <table class="wpifw_order_invoice_table">
				<?php foreach ($buttons as $button_text => $button_url):
                    // Get the order object using the appropriate method depending on HPOS status
					$order = woo_invoice_pro_hpos_enabled() ? $order : wc_get_order($post->ID);
					$show_credit_note = $order->get_total_refunded() > 0 || 'refunded' === $order->get_status();
                    if ($button_text === 'Credit Note' && !$show_credit_note) continue;
					// Function for print option
					$this->printButtonScript( $button_url ); ?>

                    <tr>
                        <td class="wpifw_button-text _winvoice-info-<?php echo sanitize_title($button_text); ?>"><?php echo esc_html($button_text); ?></td>
                        <!-- Add the Download button for each link -->
                        <td class="wpifw_order_buttons">
                            <a href="<?php echo esc_url($button_url); ?>" target="_blank">
                                <button type="button" class="wpifw_button_invoice button button-default _winvoice-info-<?php echo sanitize_title($button_text); ?>">
                                    <span class="dashicons dashicons-download"></span>
                                </button>
                            </a>
                        </td>
                        <!-- Add the Print button for each link -->
                        <td class="wpifw_order_buttons">
                            <button type="button" class="wpifw_button_invoice_print button button-default _winvoice-info-print" onclick="printButton('<?php echo esc_url($button_url); ?>')">
                                <span class="dashicons dashicons-printer"></span>
                            </button>
                        </td>
                    </tr>
				<?php endforeach; ?>
            </table>

        </div>

		<?php
	}


	/**
	 * Admin order list page bulk action handler.
	 *
	 * @param \https\Url $redirect_to Redirect url.
	 * @param string $doaction Action Name.
	 * @param array $post_ids Post Ids.
	 *
	 * @return string
	 */
	public function invoice_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
		if ( 'wpifw_bulk_invoice' !== $doaction ) {
			return $redirect_to;
		}
		if ( 'wpifw_bulk_invoice_packing_slip' !== $doaction ) {
			return $redirect_to;
		}
		if ( 'wpifw_generate_shipping_label' !== $doaction ) {
			return $redirect_to;
		}
		if ( 'wpifw_order_list' !== $doaction ) {
			return $redirect_to;
		}
		if ( 'wpifw_csv_order_list' !== $doaction ) {
			return $redirect_to;
		}

		$redirect_to = add_query_arg( 'bulk_emailed_posts', count( $post_ids ), $redirect_to );

		return $redirect_to;
	}

	/**
	 * Register bulk invoice making action
	 *
	 * @param array $bulk_actions Admin Order List table bulk action.
	 *
	 * @return array
	 */
	public function register_admin_order_list_bulk_actions( $bulk_actions ) {
		$bulk_actions['wpifw_bulk_invoice']              = __( 'Make PDF Invoice', 'webappick-pdf-invoice-for-woocommerce' );
		$bulk_actions['wpifw_bulk_invoice_packing_slip'] = __( 'Make Packing Slip', 'webappick-pdf-invoice-for-woocommerce' );
		$bulk_actions['wpifw_generate_shipping_label']   = __( 'Make Shipping Label', 'webappick-pdf-invoice-for-woocommerce' );
		$bulk_actions['wpifw_order_list']                = __( 'Make Order List (PDF)', 'webappick-pdf-invoice-for-woocommerce' );
		$bulk_actions['wpifw_csv_order_list']            = __( 'Make Order List (CSV)', 'webappick-pdf-invoice-for-woocommerce' );

		return $bulk_actions;
	}

	/**
	 * Add Admin order list table action buttons
	 *
	 * @param WC_Order $order Order Object.
	 */
	public function register_admin_order_list_actions_buttons( $order ) {
		// Get Order ID (compatibility with all WC versions).
		$order_id    = $order->get_id();
		$nonce       = wp_create_nonce( 'challan_pro_print_nonce' );
		$image_css   = 'width: 19px;margin-top: 4px;margin-left: 2px;';
		$output_type = ( get_option( 'wpifw_output_type_html' ) ) ? '&output=html' : '';

		$buttons = array(
			array(
				'type'      => 'invoice',
				'text'      => __( 'PDF Invoice', 'webappick-pdf-invoice-for-woocommerce' ),
				'src'       => CHALLAN_PRO_PLUGIN_URL . 'admin/images/invoice.svg',
				'option'    => "wpifw_invoice_action_button_$order_id",
				'action'    => 'wpifw_generate_invoice',
			),
			array(
				'type'      => 'packing_slip',
				'text'      => __( 'Packing Slip', 'webappick-pdf-invoice-for-woocommerce' ),
				'src'       => CHALLAN_PRO_PLUGIN_URL . 'admin/images/shipping_list.svg',
				'option'    => "wpifw_button_invoice_packing_slip_$order_id",
				'action'    => 'wpifw_generate_invoice_packing_slip',
			),
			array(
				'type'      => 'shipping_label',
				'text'      => __( 'Shipping Label', 'webappick-pdf-invoice-for-woocommerce' ),
				'src'       => CHALLAN_PRO_PLUGIN_URL . 'admin/images/shipping-label.svg',
				'option'    => "wpifw_invoice_shipping_label_$order_id",
				'action'    => 'wpifw_generate_shipping_label',
			),
			array(
				'type'      => 'credit_note',
				'text'      => __( 'Credit Note', 'webappick-pdf-invoice-for-woocommerce' ),
				'src'       => CHALLAN_PRO_PLUGIN_URL . 'admin/images/credit-note.svg',
				'option'    => "wpifw_invoice_credit_note_$order_id",
				'action'    => 'wpifw_generate_credit_note',
				'condition' => $order->get_total_refunded() > 0 || 'refunded' === $order->get_status(),
			),
		);

		/**
		 * Filter the admin order list buttons.
		 *
		 * @param array    $buttons   Array of buttons.
		 * @param int      $order_id  Order ID.
		 */
		$buttons = apply_filters('wpifw_admin_order_list_buttons', $buttons, $order_id);

		foreach ( $buttons as $button ) {
			if ( isset( $button['condition'] ) && ! $button['condition'] ) {
				continue;
			}

			if ( $button['type'] === 'shipping_label' ) {
				$paper_size = '' != get_option( 'wpifw_shipping_lebel_paper' ) ? get_option( 'wpifw_shipping_lebel_paper' ) : 'A4';
				$font_size  = '' != get_option( 'wpifw_shipping_lebel_font_size' ) ? get_option( 'wpifw_shipping_lebel_font_size' ) : '12';
				$url        = wp_nonce_url( admin_url( "admin-ajax.php?action={$button['action']}&order_id=$order_id&paper_size=$paper_size&font=$font_size$output_type" ), 'wpifw_pdf_nonce' );
			} elseif ( $button['type'] === 'credit_note' ) {
				$output_type = get_option( 'wpifw_output_type_html' ) ? '&output=html' : '';
				$url         = wp_nonce_url( admin_url( "admin-ajax.php?action={$button['action']}&template=credit_note&order_id=$order_id$output_type" ), 'wpifw_pdf_nonce' );
			} else {
				$url = wp_nonce_url( admin_url( "admin-ajax.php?action={$button['action']}&order_id=$order_id$output_type" ), 'wpifw_pdf_nonce' );
			}

			// Add 'exists' class based on option existence
			$is_printed = get_option( $button['option'] );
			$class      = "tips parcial wpifw_{$button['type']} {$button['option']}";
			$class      .= $is_printed ? ' exists' : ''; // Add 'exists' class conditionally

			echo $this->get_document_button( $nonce, $url, $order_id, "{$button['option']}_{$order_id}", $class, $button['text'], $button['src'] ); // phpcs:ignore
		}
        // Function for Print option
		$this->printButtonScript( $url );
		// Tooltip Print button.
		echo '<div class="wpifw-tooltip-container">';
		echo '<span class="wpifw-tooltip" data-tip="Print Options">';
		echo '<a class="button wpifw-print-dropdown-button" title="Print Options" onclick="togglePrintOptionsDropdown(this)"><span class="dashicons dashicons-printer"></span></a>';
		echo '<div class="wpifw-print-option" style="display: none;"><ul class="wpifw-print-options" >';
		foreach ($buttons as $button) {
			$show_credit_note = $order->get_total_refunded() > 0 || 'refunded' === $order->get_status();
			if ( $button['type'] === 'credit_note' && !$show_credit_note) continue;
			if ( $button['type'] === 'shipping_label' ) {
				$paper_size = '' != get_option( 'wpifw_shipping_lebel_paper' ) ? get_option( 'wpifw_shipping_lebel_paper' ) : 'A4';
				$font_size  = '' != get_option( 'wpifw_shipping_lebel_font_size' ) ? get_option( 'wpifw_shipping_lebel_font_size' ) : '12';
				$url        = wp_nonce_url( admin_url( "admin-ajax.php?action={$button['action']}&order_id=$order_id&paper_size=$paper_size&font=$font_size$output_type" ), 'wpifw_pdf_nonce' );
			} elseif ( $button['type'] === 'credit_note' ) {
				$url = wp_nonce_url( admin_url( "admin-ajax.php?action={$button['action']}&template=credit_note&order_id=$order_id$output_type" ), 'wpifw_pdf_nonce' );
			} else {
				$url = wp_nonce_url( admin_url( "admin-ajax.php?action={$button['action']}&order_id=$order_id$output_type" ), 'wpifw_pdf_nonce' );
			}
			$print_text = __('Print', 'webappick-pdf-invoice-for-woocommerce') . ' ' . esc_html($button['text']);

			echo "<li><a class='wpifw_invoice_print' title='" . esc_html($button['text']) . "' onclick='printButton(\"" . esc_url($url) . "\"); closePrintOptionsDropdowns();'>" . esc_html($print_text) . '</a></li>';
		}
		echo '</ul></div>';
		echo '</span>';
		echo '</div>';
	}

	/**
	 * Get shop_order page button icon.
	 *
	 * @param $nonce
	 * @param $url
	 * @param $order_id
	 * @param $which
	 * @param $class
	 * @param $button_text
	 * @param $image_src
	 *
	 * @return false|string
	 */
	public function get_document_button( $nonce, $url, $order_id, $which, $class, $button_text, $image_src ) {
		$image_css = 'width: 19px;margin-top: 4px;margin-left: 2px;';
		$params    = implode( ',', [
			$nonce,
			$url,
			$order_id,
			$which,
			get_option( 'wpifw_pdf_invoice_button_behaviour', 'new_tab' )
		] );

		// Add 'exists' class to the button based on option existence
		$class .= get_option( $which ) ? ' exists' : '';

		ob_start();
		?>
        <a onclick="challan_pro_save_is_document_printed( '<?php echo esc_js( $params ); ?>');return false;"
           data-which="<?php echo esc_attr( $which ) ?>"
           id="<?php echo esc_attr( $which ) ?>"
           data-nonce="<?php echo esc_attr( $nonce ) ?>" href="<?php echo esc_html( $url ) ?>" target="_blank" class="button <?php echo esc_attr( $class ) ?>"
           data-tip="<?php echo esc_html( $button_text ) ?>" title="<?php echo esc_html( $button_text ) ?>"> <img
                    src="<?php echo esc_url( $image_src ) ?>" style="<?php echo esc_html( $image_css ) ?>"/></a>
		<?php

		$button = ob_get_contents();
		ob_end_clean();

		return $button;

	}
	/**
	 * Ajax Action For Hiding Compatibility Notices
	 */
	public function challan_pro_is_document_printed() {
		check_ajax_referer( 'challan_pro_print_nonce' );

		if ( isset( $_REQUEST['which'] ) && ! empty( $_REQUEST['which'] ) && ! empty( $_REQUEST['order_id'] ) ) {
			$option_name = sanitize_text_field( wp_unslash( $_REQUEST['which'] ) );
			update_option( $option_name, true );
			wp_send_json_success( true );
			wp_die();
		}
		wp_send_json_error( esc_html__( 'Invalid Request.', 'webappick-pdf-invoice-for-woocommerce' ) );
		wp_die();
	}

	/**
	 * Attach Invoice with Order Email
	 *
	 * @param array $attachments Order email attachments.
	 * @param string $status Email Type.
	 * @param WC_Order $order Order object.
	 *
	 * @return array
	 * @throws \Mpdf\MpdfException PDF Output.
	 */
    public function attach_invoice_to_order_email( $attachments, $status, $order ) {

        if ( ! $order instanceof WC_Order ) {
            return $attachments;
        }

        // Don't attach invoice for free orders if the option is disabled.
        if ( empty( get_option( 'wpifw_free_order_attachment' ) ) && $order->get_total() == '0.00' ) {
            return $attachments;
        }

        // Get the order ID.
        $order_id = $order->get_id();

        // Retrieve the allowed statuses for invoice attachment from wpifw_email_attach_check_list.
        $allowed_statuses = ( get_option( 'wpifw_email_attach_check_list' ) == false || is_null( get_option( 'wpifw_email_attach_check_list' ) ) ) ? array() : get_option( 'wpifw_email_attach_check_list' );

        // Set default allowed statuses if no statuses are selected.
        if ( empty( $allowed_statuses ) ) {
            $allowed_statuses = array(
                'new_order',
                'customer_processing_order',
                'customer_invoice',
                'customer_completed_order',
                'customer_on_hold_order',
                'customer_refunded_order',
                'customer_partially_refunded_order',
                'customer_note',
                'cancelled_order',
                'failed_order',
            );

            // Add subscription-related statuses if WooCommerce Subscriptions is active.
            if ( in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                array_push( $allowed_statuses, 'new_renewal_order', 'customer_completed_renewal_order', 'customer_renewal_invoice' );
            }
        }

        // Allow other plugins/themes to modify the allowed statuses.
        $allowed_statuses = apply_filters( 'woo_invoice_email_types', $allowed_statuses );

        // Delete old PDF files before generating a new one.
        array_map( 'unlink', glob( CHALLAN_PRO_INVOICE_DIR . '*.pdf' ) ); // Delete files.

        // Generate & save the invoice.
        WPIFW_PDF( $order_id )->savePdf( $order_id );

        // Get the invoice number.
        $invoice_no = ( ! empty( woo_invoice_pro_get_meta( $order, 'wpifw_invoice_no_' . $order_id, true ) ) ) ? woo_invoice_pro_get_meta( $order, 'wpifw_invoice_no_' . $order_id, true ) : woo_invoice_pro_get_invoice_number( $order_id );

        // Set the file name for the invoice or credit note based on the email status.
        if ( isset( $status ) && ( 'customer_refunded_order' === $status ) ) {
            $file_name = 'Credit-Note-Of-Invoice-' . $invoice_no;
            $file_name = apply_filters( 'woo_invoice_file_name', $file_name, 'credit_note', $order_id );
        } else {
            $file_name = 'Invoice-' . $invoice_no;
            $file_name = apply_filters( 'woo_invoice_file_name', $file_name, 'invoice', $order_id );
        }

        // Attach the invoice based on the email status.
        if ( in_array( 'always_allow', $allowed_statuses ) ) {
            $pdf_path      = CHALLAN_PRO_INVOICE_DIR . $file_name . '.pdf';
            $attachments[] = $pdf_path;
        } elseif ( isset( $status ) && is_current_status_checked( $status, $allowed_statuses ) ) {
            $pdf_path      = CHALLAN_PRO_INVOICE_DIR . $file_name . '.pdf';
            $attachments[] = $pdf_path;
        }

        // Handle static file attachments (if needed).
        $static_allowed_statuses = ( get_option( 'wpifw_static_attach_check_list' ) != false ) ? get_option( 'wpifw_static_attach_check_list' ) : array();
        if ( empty( $static_allowed_statuses ) ) {
            $static_allowed_statuses = array(
                'new_order',
                'customer_processing_order',
                'customer_invoice',
                'customer_on_hold_order',
                'customer_completed_order',
                'customer_refunded_order',
                'customer_partially_refunded_order',
                'customer_note',
                'cancelled_order',
                'failed_order',
            );
        }

        if ( in_array( 'always_allow', $allowed_statuses ) ) {
            for ( $i = 0; $i < 3; $i ++ ) {
                if ( get_option( 'wpifw_static_files_' . $i ) != false && ! empty( 'wpifw_static_files_' . $i ) ) {
                    $id             = attachment_url_to_postid( get_option( 'wpifw_static_files_' . $i ) );
                    $full_size_path = get_attached_file( $id );
                    $attachments[]  = $full_size_path;
                }
            }
        } elseif ( isset( $status ) && is_current_status_checked( $status, $static_allowed_statuses ) ) {
            for ( $i = 0; $i < 3; $i ++ ) {
                if ( get_option( 'wpifw_static_files_' . $i ) != false && ! empty( 'wpifw_static_files_' . $i ) ) {
                    $id             = attachment_url_to_postid( get_option( 'wpifw_static_files_' . $i ) );
                    $full_size_path = get_attached_file( $id );
                    $attachments[]  = $full_size_path;
                }
            }
        }

        // Allow other plugins/themes to add additional attachments.
        $attachments = apply_filters( 'wpifw_new_order_attachments', $attachments, $status, $order );

        return $attachments;
    }

    /**
     * Attach Packing Slip with Order Email
     *
     * @param array $attachments Order email attachments.
     * @param string $status Email Type.
     * @param WC_Order $order Order object.
     *
     * @return array
     */
    public function attach_packing_slip_to_order_email( $attachments, $status, $order ) {

        if ( ! $order instanceof WC_Order ) {
            return $attachments;
        }

        // Get the order ID.
        $order_id = $order->get_id();

        // Check if the order contains a subscription (parent or renewal).
        if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id, array( 'parent', 'renewal' ) ) ) {
            // If the order contains a subscription, do not attach the packing slip.
            return $attachments;
        }

        // Retrieve the allowed statuses for packing slip from the wpifw_email_attach_packing_slip_list option.
        $allowed_statuses = get_option( 'wpifw_email_attach_packing_slip_list', array() );

        // If no statuses are selected, make 'customer_completed_order' enabled by default.
        if ( empty( $allowed_statuses ) ) {
            $allowed_statuses = array( 'customer_completed_order' );
        }

        // Apply a filter for the allowed statuses for packing slip.
        $allowed_statuses = apply_filters( 'woo_packing_slip_email_types', $allowed_statuses );

        // Check if packing slips are enabled.
        if ( '1' === get_option( 'wpifw_email_packing_slip' ) ) {

            // Generate & save the packing slip. This assumes you have a function to generate the packing slip PDF.
            $invoice_no = ( ! empty( woo_invoice_pro_get_meta( $order, 'wpifw_invoice_no_' . $order_id, true ) ) ) ? woo_invoice_pro_get_meta( $order, 'wpifw_invoice_no_' . $order_id, true ) : woo_invoice_pro_get_invoice_number( $order_id );

            // Packing slip file name.
            $file_name = 'Packing-slip-' . $invoice_no;
            $file_name = apply_filters( 'woo_invoice_file_name', $file_name, 'packing_slip', $order_id );

            // Attach packing slip based on the selected email status.
            if ( in_array( 'always_allow', $allowed_statuses ) ) {
                $pdf_path      = CHALLAN_PRO_INVOICE_DIR . $file_name . '.pdf';
                $attachments[] = $pdf_path;
            } elseif ( isset( $status ) && is_current_status_checked( $status, $allowed_statuses ) ) {
                $pdf_path      = CHALLAN_PRO_INVOICE_DIR . $file_name . '.pdf';
                $attachments[] = $pdf_path;
            }
        }

        // Return the updated attachments array.
        return $attachments;
    }

    /**
	 * Add QR code to WC email.
	 *
	 */
	public function add_qr_code_to_email( $order, $sent_to_admin, $plain_text, $email ) {
		echo do_shortcode( "[challan_pro_qr_code order_id='" . $order->get_id() . "']" );
	}


	/**
	 * Add Invoice Download link to order email
	 *
	 * @param WC_Order $order Order Object.
	 */
	public function add_invoice_download_link( $order ) {
		$upload   = wp_upload_dir();
		$base_url = $upload['baseurl'];

		$allowed_statuses = array(
			'new_order',
			'customer_invoice',
			'completed',
			'customer_on_hold_order',
			'completed_renewal_order',
			'customer_renewal_invoice',
			'new_renewal_order',
		);

		if ( get_option( 'wpifw_proforma_invoicing' ) == '1' ) {
			array_push( $allowed_statuses, 'customer_processing_order' );
			array_push( $allowed_statuses, 'customer_processing_renewal_order' );
		}

		$status = $order->get_status();

		$order_id = $order->get_id();


		if ( ! empty( woo_invoice_pro_get_meta( $order_id, 'wpifw_invoice_no_' . $order_id, true ) ) ) {
			$invoice_id = woo_invoice_pro_get_meta( $order_id, 'wpifw_invoice_no_' . $order_id, true );
		} else {
			$invoice_id = woo_invoice_pro_get_meta( $order_id, 'wpifw_invoice_no', true );
		}


		if ( file_exists( CHALLAN_PRO_INVOICE_DIR . 'Invoice-' . $invoice_id . '.pdf' ) && ( isset( $status ) && in_array( $status, $allowed_statuses ) ) ) {
			$download_link         = $base_url . '/WPIFW-INVOICE/Invoice-' . $invoice_id . '.pdf';
			$download_invoice_text = get_option( 'wpifw_DOWNLOAD_INVOICE_TEXT' );
			$download_invoice_text = ( $download_invoice_text ) ? $download_invoice_text : 'Download Invoice';
			echo '<a href="' . esc_url( $download_link ) . '" target="_blank"  class="button wpifw_invoice" data-tip="" title="">' . esc_html( $download_invoice_text ) . '</a> <br><br/>';
		}

	}

    /**
     * Generate Next Invoice Sequence
     */
    public function add_invoice_number_to_order($order_id) {

        // Check if WooCommerce Subscriptions plugin is active

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $order = woo_invoice_pro_get_order( $order_id );
        if (!$order || !should_generate_invoice($order)) {
            return;
        }

        $invoice_no = $order->get_order_number();

        // Get next number for custom sequence.
        $next_no = get_option('wpifw_invoice_no');
        $next_no = !empty($next_no) ? $next_no : 1;

        $get_number_type = get_option('wpifw_invoice_number_type');

        // Get Prefix.
        $prefix = get_option('wpifw_invoice_no_prefix');
        $prefix = !empty($prefix) ? $prefix : '';

        // Get Suffix.
        $suffix = get_option('wpifw_invoice_no_suffix');
        $suffix = !empty($suffix) ? $suffix : '';

        // Generate Invoice Number.
        if ('pre_custom_number_suf' === $get_number_type) {
            $invoice_no = $prefix . $next_no . $suffix;
        } elseif ('pre_order_number_suf' === $get_number_type) {
            $invoice_no = $prefix . $invoice_no . $suffix;
        }

        $invoice_no = woo_invoice_pro_process_date_macros($order_id, $invoice_no);

        woo_invoice_pro_update_meta($order, 'wpifw_invoice_no', $invoice_no);

        // Conditionally update post meta based on WooCommerce Subscriptions plugin status
        if (is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php')) {
            woo_invoice_pro_update_meta($order, 'wpifw_invoice_no_' . $order_id, $invoice_no);
        }

        // Increment the invoice number for the next order and update the option.
        ++$next_no;
        update_option('wpifw_invoice_no', $next_no);
    }

    /**
     * Handles the order status change and generates an invoice number if needed.
     *
     * This function is triggered when an order's status changes. It checks if the order
     * already has an invoice number. If not, and the new status matches the configured
     * statuses for invoice generation, it generates and assigns an invoice number.
     *
     * @param int      $order_id   The ID of the order.
     * @param string   $old_status The previous status of the order.
     * @param string   $new_status The new status of the order.
     * @param WC_Order $order      The order object (may sometimes be null or not passed as expected).
     */
    public function handle_order_status_changed($order_id, $old_status, $new_status, $order) {
        // Ensure we have a valid WC_Order object (fallback in case it's not passed)
        if (!is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
        }

        // Retrieve the existing invoice number from the order's metadata
        $existing_invoice_no = woo_invoice_pro_get_meta($order, 'wpifw_invoice_no', true);

        // If no invoice number is found, and the new status is allowed, generate one
        if (empty($existing_invoice_no)) {
            // Only generate if the new status matches the allowed statuses (e.g., 'completed')
            if (should_generate_invoice($order)) {
                // Generate and assign a new invoice number for this order
                $this->add_invoice_number_to_order($order_id);
            }
        }
    }

    /**
     * Generate Next Credit Note Sequence
     */
    public function add_credit_note_number_to_order( $order_id ) {

        $credit_note_no = wc_get_order( $order_id )->get_order_number();

        // Get next number for custom sequence.
        $next_no = get_option( 'wpifw_credit_note_no' );
        $next_no = ! empty( $next_no ) ? $next_no : 1;

        $get_number_type = get_option( 'wpifw_credit_note_number_type' );

        // Get Prefix.
        $prefix = get_option( 'wpifw_credit_note_no_prefix' );
        $prefix = ! empty( $prefix ) ? $prefix : '';

        // Get Suffix.
        $suffix = get_option( 'wpifw_credit_note_no_suffix' );
        $suffix = ! empty( $suffix ) ? $suffix : '';

        // Generate Credit Note Number.
        if ( 'pre_custom_number_suf' === $get_number_type ) {
            $credit_note_no = $prefix . $next_no . $suffix;
        } elseif ( 'pre_order_number_suf' === $get_number_type ) {
            $credit_note_no = $prefix . $credit_note_no . $suffix;
        }

        $credit_note_no = woo_invoice_pro_process_date_macros( $order_id, $credit_note_no );
        woo_invoice_pro_update_meta( $order_id, 'wpifw_credit_note_no', $credit_note_no );

        // Increment the credit note number for the next order and update the option.
        ++$next_no;
        update_option( 'wpifw_credit_note_no', $next_no );
    }


    /**
	 *  Add Logo uploader script to footer
	 */
	public function logo_selector_print_scripts() {
		?>
        <script type='text/javascript'>

            /*--------- Custom Invoice Logo Javascript ----------*/
            jQuery(document).ready(function ($) {

                jQuery(document).on("click", "#wpifw_upload_logo_button", function (e) {
                    e.preventDefault();
                    var $button = $(this);


                    // Create the media frame.
                    var file_frame = wp.media.frames.file_frame = wp.media({
                        title: 'Select or upload image',
                        library: { // remove these to show all
                            type: 'image' // specific mime
                        },
                        button: {
                            text: 'Select'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    // When an image is selected, run a callback.
                    file_frame.on('select', function () {
                        // We set multiple to false so only get one image from the uploader

                        var attachment = file_frame.state().get('selection').first().toJSON();

                        $('#wpifw_logo-preview').attr('src', attachment.url).css('width', 'auto');
                        $('#wpifw_logo_attachment_id').val(attachment.id);

                        $button.siblings('input').val(attachment.id).change();

                    });

                    // Finally, open the modal
                    file_frame.open();
                });
            });

            /*--------- Custom Stamp Javascript ----------*/
            jQuery(document).ready(function ($) {

                jQuery(document).on("click", "#wpifw_upload_custom_stamp_button", function (e) {
                    e.preventDefault();
                    var $button = $(this);

                    // Create the media frame.
                    var file_frame = wp.media.frames.file_frame = wp.media({
                        title: 'Select or upload image',
                        library: { // remove these to show all
                            type: 'image' // specific mime
                        },
                        button: {
                            text: 'Select'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    // When an image is selected, run a callback.
                    file_frame.on('select', function () {
                        // We set multiple to false so only get one image from the uploader

                        var attachment = file_frame.state().get('selection').first().toJSON();

                        $('#wpifw_custom_stamp_preview').attr('src', attachment.url);
                        $('#wpifw_custom_stamp_attachment_id').val(attachment.id);
                        $button.siblings('input').val(attachment.id).change();

                    });

                    // Finally, open the modal
                    file_frame.open();
                });
            });


            /*--------- Signature Javascript ----------*/
            jQuery(document).ready(function ($) {

                jQuery(document).on("click", "#wpifw_upload_signature_button", function (e) {
                    e.preventDefault();
                    var $button = $(this);


                    // Create the media frame.
                    var file_frame = wp.media.frames.file_frame = wp.media({
                        title: 'Select or upload image',
                        library: { // remove these to show all
                            type: 'image' // specific mime
                        },
                        button: {
                            text: 'Select'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    // When an image is selected, run a callback.
                    file_frame.on('select', function () {
                        // We set multiple to false so only get one image from the uploader

                        var attachment = file_frame.state().get('selection').first().toJSON();

                        $('#wpifw_signature-preview').attr('src', attachment.url);
                        $('#wpifw_signature_attachment_id').val(attachment.id);
                        $button.siblings('input').val(attachment.id).change();

                    });

                    // Finally, open the modal
                    file_frame.open();
                });
            });


            /*------------- Custom Background Javascript ----------*/
            jQuery(document).ready(function ($) {

                jQuery(document).on("click", "#wpifw_upload_invoice_background_button", function (e) {
                    e.preventDefault();
                    var $button = $(this);


                    // Create the media frame.
                    var file_frame = wp.media.frames.file_frame = wp.media({
                        title: 'Select or upload image',
                        library: { // remove these to show all
                            type: 'image' // specific mime
                        },
                        button: {
                            text: 'Select'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    // When an image is selected, run a callback.
                    file_frame.on('select', function () {
                        // We set multiple to false so only get one image from the uploader

                        var attachment = file_frame.state().get('selection').first().toJSON();

                        $('#wpifw_invoice-background-preview').attr('src', attachment.url);
                        $('#wpifw_invoice_background_attachment_id').val(attachment.id);

                        $button.siblings('input').val(attachment.id).change();

                    });

                    // Finally, open the modal
                    file_frame.open();
                });
            });


            /*------------- Upload Static file Javascript ----------*/
            jQuery(document).ready(function ($) {

                $(".wpifw_static_files_section .wpifw_static_files").each(function (index) {
                    jQuery(document).on("click", "#wpifw_upload_static_files_" + index + "_button", function (e) {
                        e.preventDefault();
                        var $button = $(this);


                        // Create the media frame.
                        var file_frame = wp.media.frames.file_frame = wp.media({
                            title: 'Select or upload image',
                            /*library: { // remove these to show all
								type: 'application/pdf' // specific mime
							},*/
                            button: {
                                text: 'Select'
                            },
                            multiple: false  // Set to true to allow multiple files to be selected
                        });

                        // When an image is selected, run a callback.
                        file_frame.on('select', function () {
                            // We set multiple to false so only get one image from the uploader

                            var attachment = file_frame.state().get('selection').first().toJSON();

                            $("input[name='wpifw_static_files_" + index + "']").val(attachment.url);

                            $button.siblings('#wpifw_static_files_' + index).val(attachment.url).change();

                        });

                        // Finally, open the modal
                        file_frame.open();
                    });
                });


            });


            /*------------ Upload Packing Slip Background Javascript ----------*/
            jQuery(document).ready(function ($) {

                jQuery(document).on("click", "#wpifw_upload_packingslip_background_button", function (e) {
                    e.preventDefault();
                    var $button = $(this);


                    // Create the media frame.
                    var file_frame = wp.media.frames.file_frame = wp.media({
                        title: 'Select or upload image',
                        library: { // remove these to show all
                            type: 'image' // specific mime
                        },
                        button: {
                            text: 'Select'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    // When an image is selected, run a callback.
                    file_frame.on('select', function () {
                        // We set multiple to false so only get one image from the uploader

                        var attachment = file_frame.state().get('selection').first().toJSON();

                        $('#wpifw_packingslip-background-preview').attr('src', attachment.url).css('width', 'auto');
                        $('#wpifw_packingslip_background_attachment_id').val(attachment.id);

                        $button.siblings('input').val(attachment.id).change();

                    });

                    // Finally, open the modal
                    file_frame.open();
                });
            });

        </script>
		<?php
	}

    /**
     * Add VAT / SSN / GSTIN fields to WooCommerce My Account edit form
     */
    public function woo_invoice_pro_add_vat_ssn_to_edit_account_form() {
        if ( ! apply_filters( 'woo_invoice_pro_display_vat_ssn_fields', true ) ) {
            return;
        }

        $user = wp_get_current_user();

        // Helper to normalize "truthy" options
        $boolify = function ( $key, $default = 'no' ) {
            $val = get_option( $key, $default );
            return function_exists( 'wc_string_to_bool' )
                ? wc_string_to_bool( $val )
                : in_array( $val, array( 'yes', '1', 1, true ), true );
        };

        $fields = array(
            'vat'   => array(
                'option' => 'wpifw_display_vat_id',
                'label'  => __( 'VAT Number', 'webappick-pdf-invoice-for-woocommerce' ),
                'key'    => 'wpifw_vat_id',
            ),
            'ssn'   => array(
                'option' => 'wpifw_display_ssn',
                'label'  => __( 'SSN', 'webappick-pdf-invoice-for-woocommerce' ),
                'key'    => 'wpifw_ssn_id',
            ),
            'gstin' => array(
                'option' => 'wpifw_display_gstin',
                'label'  => __( 'GSTIN', 'webappick-pdf-invoice-for-woocommerce' ),
                'key'    => 'wpifw_gstin_id',
            ),
        );

        foreach ( $fields as $field ) {
            if ( ! $boolify( $field['option'] ) ) {
                continue;
            }

            $value = get_user_meta( $user->ID, $field['key'], true );
            ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="<?php echo esc_attr( $field['key'] ); ?>">
                    <?php echo esc_html( $field['label'] ); ?>
                    <input type="text"
                           class="woocommerce-Input woocommerce-Input--text input-text"
                           name="<?php echo esc_attr( $field['key'] ); ?>"
                           id="<?php echo esc_attr( $field['key'] ); ?>"
                           value="<?php echo esc_attr( $value ); ?>" />
                </label>
            </p>
            <?php
        }
    }

    /**
     * Save VAT / SSN / GSTIN fields from My Account edit form
     */
    public function woo_invoice_pro_save_vat_ssn_account_details( $user_id ) {
        $keys = array( 'wpifw_vat_id', 'wpifw_ssn_id', 'wpifw_gstin_id' );
        foreach ( $keys as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_user_meta( $user_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
    }

    /**
     * Add VAT / SSN / GSTIN fields to WordPress Edit User Page
     */
    public function woo_invoice_pro_wp_vat_ssn_fields( $user ) {
        $fields = array(
            'vat'   => array(
                'option' => 'wpifw_display_vat_id',
                'label'  => __( 'VAT Number', 'webappick-pdf-invoice-for-woocommerce' ),
                'key'    => 'wpifw_vat_id',
                'desc'   => __( 'Please enter your VAT ID for invoices.', 'webappick-pdf-invoice-for-woocommerce' ),
            ),
            'ssn'   => array(
                'option' => 'wpifw_display_ssn',
                'label'  => __( 'SSN', 'webappick-pdf-invoice-for-woocommerce' ),
                'key'    => 'wpifw_ssn_id',
                'desc'   => __( 'Please enter your SSN for invoices.', 'webappick-pdf-invoice-for-woocommerce' ),
            ),
            'gstin' => array(
                'option' => 'wpifw_display_gstin',
                'label'  => __( 'GSTIN', 'webappick-pdf-invoice-for-woocommerce' ),
                'key'    => 'wpifw_gstin_id',
                'desc'   => __( 'Please enter your GSTIN for invoices.', 'webappick-pdf-invoice-for-woocommerce' ),
            ),
        );
        ?>
        <h3><?php esc_html_e( 'Tax Identification Information', 'webappick-pdf-invoice-for-woocommerce' ); ?></h3>
        <table class="form-table">
            <?php foreach ( $fields as $field ) :
                $show = in_array( get_option( $field['option'], 'no' ), array( 'yes', '1', 1, true ), true );
                if ( ! $show ) {
                    continue;
                }
                $value = get_the_author_meta( $field['key'], $user->ID );
                ?>
                <tr>
                    <th><label for="<?php echo esc_attr( $field['key'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
                    <td>
                        <input type="text" name="<?php echo esc_attr( $field['key'] ); ?>"
                               id="<?php echo esc_attr( $field['key'] ); ?>"
                               value="<?php echo esc_attr( $value ); ?>"
                               class="regular-text" />
                        <br><span class="description"><?php echo esc_html( $field['desc'] ); ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
    }

    /**
     * Save VAT / SSN / GSTIN fields on WordPress Edit User page
     */
    public function woo_invoice_pro_wp_vat_ssn_fields_save( $user_id ) {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        $keys = array( 'wpifw_vat_id', 'wpifw_ssn_id', 'wpifw_gstin_id' );
        foreach ( $keys as $key ) {
            $value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
            update_user_meta( $user_id, $key, $value );
        }
    }

    /**
	 * Add VAT ID AND SSN field to the checkout page
	 *
	 * @param mixed $fields Checkout Fields.
	 *
	 * @return mixed
	 */
	public function woo_invoice_pro_add_checkout_field( $fields ) {
		if ( get_option( 'wpifw_display_vat_id' ) == true ) {
			$vat_id = ( get_option( 'wpifw_VAT_ID' ) ) ? get_option( 'wpifw_VAT_ID' ) : esc_html__( 'VAT Number', 'webappick-pdf-invoice-for-woocommerce' );
			$vat_id = apply_filters( 'woo_invoice_pro_checkout_vat_id_label', $vat_id );
            $current_user = wp_get_current_user();
            $field_name = '_billing_wpifw_vat_id';
			$av = get_user_meta( $current_user->ID, '_billing_wpifw_vat_id' );

			$fields['wpifw_vat_id'] = array(
				'label'    => $vat_id,
				'type'     => 'text',
				'class'    => array( 'form-row-wide' ),
				'priority' => 101,
				'required' => false,
			);
		}

		if ( get_option( 'wpifw_display_ssn' ) == true ) {
			$ssn = ( get_option( 'wpifw_SSN' ) ) ? get_option( 'wpifw_SSN' ) : esc_html__( 'SSN', 'webappick-pdf-invoice-for-woocommerce' );
			$ssn = apply_filters( 'woo_invoice_pro_checkout_ssn_label', $ssn );
			$fields['wpifw_ssn_id'] = array(
				'label'    => $ssn,
				'type'     => 'text',
				'class'    => array( 'form-row-wide' ),
				'priority' => 102,
				'required' => false,
			);
		}

		if ( get_option( 'wpifw_display_gstin' ) == true ) {
			$gstin = ( get_option( 'wpifw_GSTIN' ) ) ? get_option( 'wpifw_GSTIN' ) : esc_html__( 'GSTIN', 'webappick-pdf-invoice-for-woocommerce' );
			$gstin = apply_filters( 'woo_invoice_pro_checkout_gstin_label', $gstin );
			$fields['wpifw_gstin_id'] = array(
				'label'    => $gstin,
				'type'     => 'text',
				'class'    => array( 'form-row-wide' ),
				'priority' => 103,
				'required' => false,
			);
		}

		return $fields;
	}

	/**
	 * Update VAT, SSN and GSTIN checkout field value
	 * phpcs:disable
	 *
	 * @param int $order_id Order id.
	 */
	public function woo_invoice_pro_checkout_field_update_order_meta( $order_id ) {
		if ( isset( $_POST['wpifw_vat_id'] ) && ! empty( $_POST['wpifw_vat_id'] ) ) {
            woo_invoice_pro_update_meta( $order_id, 'wpifw_vat_id', sanitize_text_field( $_POST['wpifw_vat_id'] ) );
			update_user_meta( get_current_user_id(), 'wpifw_vat_id', sanitize_text_field( $_POST['wpifw_vat_id'] ) );
		}
		if ( isset( $_POST['wpifw_ssn_id'] ) && ! empty( $_POST['wpifw_ssn_id'] ) ) {
            woo_invoice_pro_update_meta( $order_id, 'wpifw_ssn_id', sanitize_text_field( $_POST['wpifw_ssn_id'] ) );
			update_user_meta( get_current_user_id(), 'wpifw_ssn_id', sanitize_text_field( $_POST['wpifw_ssn_id'] ) );
		}
		if ( isset( $_POST['wpifw_gstin_id'] ) && ! empty( $_POST['wpifw_gstin_id'] ) ) {
            woo_invoice_pro_update_meta( $order_id, 'wpifw_gstin_id', sanitize_text_field( $_POST['wpifw_gstin_id'] ) );
			update_user_meta( get_current_user_id(), 'wpifw_gstin_id', sanitize_text_field( $_POST['wpifw_gstin_id'] ) );
		}
	}//phpcs:enable

	/**
	 * Add HSN/SAC field to product general tab
	 */
	public function add_hsn_sac_field_to_product_general_tab() {
		woocommerce_wp_text_input(
			array(
				'id'                => 'hsn_sac',
				'label'             => __('HSN/SAC Code', 'webappick-pdf-invoice-for-woocommerce'),
				'description'       => __('Enter the HSN/SAC code for this product.', 'webappick-pdf-invoice-for-woocommerce'),
			//	'custom_attributes' => array('required' => 'required'),
				'value'             => get_post_meta(get_the_ID(), 'hsn_sac', true)
			)
		);
	}

	/**
	 * Save HSN/SAC field data
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_hsn_sac_field_data($post_id) {
		$value = isset($_POST['hsn_sac']) ? sanitize_text_field($_POST['hsn_sac']) : '';
        update_post_meta($post_id, 'hsn_sac', $value);
	}

	/**
	 * Process invoice template number
	 */
	public function woo_invoice_pro_save_pdf_template() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$template = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : 'invoice-1';
		update_option( 'wpifw_templateid', $template );
		$response = plugin_dir_url( __DIR__ ) . '/admin/images/templates/' . esc_html( $template );
		wp_send_json_success( $response );
		wp_die();
	}

	/**
	 * Enable paid stamp show
	 */
	public function woo_invoice_pro_save_paid_stamp() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );

		$stamp = isset( $_POST['stamp'] ) ? sanitize_text_field( wp_unslash( $_POST['stamp'] ) ) : '';
		update_option( 'wpifw_paid_stamp_image', $stamp );
		$stamp_response = plugin_dir_url( __DIR__ ) . '/admin/images/paid-stamp/' . esc_html( $stamp );
		wp_send_json_success( $stamp_response );
	}

	/**
	 * Get paid stamp show status
	 */
	public function woo_invoice_pro_paid_stamp_enabled() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		update_option( 'wpifw_paid_stamp', true );
		wp_send_json_success();
	}

	/**
	 * Enable Product Attribute Show
	 */
	public function woo_invoice_pro_product_attribute_show() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$attributes = array();
		if ( isset( $_POST['attribute'] ) ) {
			foreach ( $_POST['attribute'] as $key => $value ) { //phpcs:ignore
				$attributes[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
			}
		}
		$value = ! empty( $attributes ) ? $attributes : '';
		update_option( 'wpifw_product_attribute_show', $value );
		wp_send_json_success();
	}

	/**
	 * Save Product Header.
	 */
	public function wpifw_select_product_column() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$columns = array();
		if ( isset( $_POST['columns'] ) ) {
			foreach ( $_POST['columns'] as $key => $value ) { //phpcs:ignore
				$columns[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
			}
		}
		$value = ! empty( $columns ) ? $columns : '';
		update_option( 'wpifw_select_product_column', $value );
		wp_send_json_success();
	}

	/**
	 * Save Product Header For Packing Slip.
	 */
	public function wpifw_packingslip_product_table_header() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$columns = array();
		if ( isset( $_POST['columns'] ) ) {
			foreach ( $_POST['columns'] as $key => $value ) { //phpcs:ignore
				$columns[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
			}
		}
		$value = ! empty( $columns ) ? $columns : '';
		update_option( 'wpifw_packingslip_product_table_header', $value );
		wp_send_json_success();
	}

	/**
	 * Save CSV Fields.
	 */
	public function woo_invoice_pro_wpifw_save_csv_fields() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$attributes = array();
		if ( isset( $_POST['attribute'] ) ) {
			foreach ( $_POST['attribute'] as $key => $value ) { //phpcs:ignore
				$attributes[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
			}
		}
		$value = ! empty( $attributes ) ? $attributes : '';
		update_option( 'wpifw_add_fields_csv', $value );
		wp_send_json_success();
	}

	/**
	 * Display bulk download csv fields.
	 */
	public function woo_invoice_pro_get_csv_fields_show() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$wpifw_add_fields_csv = get_option( 'wpifw_add_fields_csv' );
		wp_send_json_success( $wpifw_add_fields_csv );
	}


	/**
	 * Get Product Attribute Status
	 */
	public function woo_invoice_pro_get_product_attribute_show() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$wpifw_product_attribute_show = get_option( 'wpifw_product_attribute_show' );
		wp_send_json_success( $wpifw_product_attribute_show );
	}

	/**
	 * Get Product Column data
	 */
	public function woo_invoice_pro_get_product_column() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$wpifw_product_column_show = get_option( 'wpifw_select_product_column' );
		wp_send_json_success( $wpifw_product_column_show );
	}

	/**
	 * Get Product Column for packing slip
	 */
	public function get_wpifw_packingslip_product_table_header() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$wpifw_product_column_show = get_option( 'wpifw_packingslip_product_table_header' );
		wp_send_json_success( $wpifw_product_column_show );
	}


	/**
	 * Enable Product Dimension Show
	 */
	public function woo_invoice_pro_product_dimension_show() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );

		$dimensions = array();
		foreach ( $_POST['dimension'] as $key => $value ) { //phpcs:ignore
			$dimensions[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
		}
		$value = isset( $_POST['dimension'] ) ? $dimensions : '';
		update_option( 'wpifw_invoice_product_dimension_show', $value );
		wp_send_json_success();
	}


	/**
	 * Get Product Show Status
	 */
	public function woo_invoice_pro_get_product_dimension_show() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$wpifw_invoice_product_dimension_show = get_option( 'wpifw_invoice_product_dimension_show' ) ? get_option( 'wpifw_invoice_product_dimension_show' ) : '';
		wp_send_json_success( $wpifw_invoice_product_dimension_show );
	}

	/**
	 * Show Review request admin notice
	 */
	public function woo_invoice_pro_save_review_notice() {
		// Verify Nonce.
		check_ajax_referer( 'wpifw_pdf_nonce' );
		$notice                 = isset( $_POST['notice'] ) ? sanitize_text_field( wp_unslash( $_POST['notice'] ) ) : '';
		$value['review_notice'] = $notice;
		$value['updated_at']    = time();

		update_option( 'woo_invoice_pro_review_notice', $value );
		wp_send_json_success( $value );
	}

	/**
	 * Add some links to Admin plugin list page
	 *
	 * @param array $links Plugin Links.
	 *
	 * @return array
	 */
	public function woo_invoice_pro_plugin_action_links( $links ) {
		$links[] = '<a style="color:#8e44ad;" href="' . admin_url( 'admin.php?page=webappick-woo-invoice-settings' ) . '">' . __( 'Settings', 'webappick-pdf-invoice-for-woocommerce' ) . '</a>';
		$links[] = '<a style="color:#8e44ad;" href="https://webappick.com/my-account/contact-support/" target="_blank">' . __( 'Support', 'webappick-pdf-invoice-for-woocommerce' ) . '</a>';

		return $links;
	}

	/**
	 * Extend Billing Address
	 *
	 * @param string $address Billing Address
	 * @param WC_Order $order Order Object
	 * @param string $template invoice|packing_slip
	 *
	 * @return string
	 */
	public function woo_invoice_billing_info_callback( $address, $order, $template ) {

		$order_id = $order->get_id();
		// Add SSN and VAT ID if billing address.
		if ( 'invoice' == $template ) {
			if ( get_option( 'wpifw_display_vat_id' ) ) {
				// Get VAT Number.
				if ( ! empty( woo_invoice_pro_get_meta( $order_id, 'wpifw_vat_id', true ) ) ) {
					$address .= '<br/>';
					$address .= woo_invoice_filter_label( 'VAT Number', $order, $template ) . ' : ' . woo_invoice_pro_get_meta( $order_id, 'wpifw_vat_id', true );
				} else {
					if ( get_user_meta( $order->get_user_id(), 'wpifw_vat_id', true ) != '' ) {
						$address .= '<br/>';
						$address .= woo_invoice_filter_label( 'VAT Number', $order, $template ) . ' : ' . get_user_meta( $order->get_user_id(), 'wpifw_vat_id', true );
					}
				}
			}

			if ( get_option( 'wpifw_display_ssn' ) ) {
				// Get SSN Number.
				if ( ! empty( woo_invoice_pro_get_meta( $order_id, 'wpifw_ssn_id', true ) ) ) {
					$address .= '<br/>' . woo_invoice_filter_label( 'SSN', $order, $template ) . ' : ' . woo_invoice_pro_get_meta( $order_id, 'wpifw_ssn_id', true );
				} else {
					if ( ! empty( get_user_meta( $order->get_user_id(), 'wpifw_ssn_id', true ) ) ) {
						$address .= '<br/>' . woo_invoice_filter_label( 'SSN', $order, $template ) . ' : ' . get_user_meta( $order->get_user_id(), 'wpifw_ssn_id', true );
					}
				}
			}
			// Check if the invoice type is 'gstin' to include the HSN/SAC option
			if ( get_option( 'wpifw_invoice_type' ) === 'gstin' ) {
				if ( get_option( 'wpifw_display_gstin' ) ) {
					// Get GSTIN Number.
					if ( ! empty( woo_invoice_pro_get_meta( $order_id, 'wpifw_gstin_id', true ) ) ) {
						$address .= '<br/>' . woo_invoice_filter_label( 'GSTIN', $order, $template ) . ' : ' . woo_invoice_pro_get_meta( $order_id, 'wpifw_gstin_id', true );
					} else {
						if ( ! empty( get_user_meta( $order->get_user_id(), 'wpifw_gstin_id', true ) ) ) {
							$address .= '<br/>' . woo_invoice_filter_label( 'GSTIN', $order, $template ) . ' : ' . get_user_meta( $order->get_user_id(), 'wpifw_gstin_id', true );
						}
					}
				}
			}
		}

		return $address;
	}

	/**
	 * Get after order note content. this function is for getting payment slip if "seed confirm pro" plugin is active.
	 *
	 * @param $order
	 * @param $template_type
	 */
	public function woo_invoice_add_slip_to_order_note( $order, $template_type ) {

		if ( 'invoice' === $template_type || 'credit_note' === $template_type ) {
			$args = array(
				'post_type'  => 'seed_confirm_log',
				'meta_key'   => 'seed-confirm-order',
				'meta_value' => $order->get_id()
			); //phpcs:ignore;

			$posts = get_posts( $args );

			if ( empty( $posts ) ) {
				return;
			}

			$post_id  = $posts[0]->ID;
			$file_url = woo_invoice_pro_get_meta( $post_id, 'seed-confirm-image', true );

			if ( empty( $file_url ) ) {
				return;
			}

			$filetype  = wp_check_filetype( $file_url );
			$file_icon = $file_url;
			if ( strpos( $filetype['type'], 'application' ) !== false ) {
				if ( "pdf" === $filetype['ext'] ) {
					$file_icon = plugin_dir_url( __FILE__ ) . 'img/pdf.png';
				} else {
					$file_icon = plugin_dir_url( __FILE__ ) . 'img/zip.png';
				}
			}
			$default_width  = apply_filters( 'woo_invoice_payment_slip_width', '200' );
			$default_height = apply_filters( 'woo_invoice_payment_slip_height', '200' );
			$file_html      = sprintf( wp_kses(
				'<a href="%s" target="_blank"><img src="%s" height="' . $default_height . '" width="' . $default_width . '"></a>',
				array(
					'a'   => array(
						'href'   => array(),
						'target' => array(),
					),
					'img' => array(
						'src'    => array(),
						'width'  => array(),
						'height' => array(),
					),
				)
			), esc_url( $file_url ), esc_url( $file_icon ) );

			$output = sprintf( '<div>
            <h3>' . woo_invoice_filter_label( 'Payment Slip', $order, $template_type ) . '</h3>
            <p>%1$s</p>
            </div>', $file_html );
			echo $output; //phpcs:ignore

			return;
		}

		return;

	}

	/**
	 * Get after order note content. this function is for getting payment slip if "wc-confirm-payment" plugin is active.
	 *
	 * @param $order
	 * @param $template_type
	 */
	public function woo_invoice_add_payment_slip_to_order_note( $order, $template_type ) {

		if ( 'invoice' === $template_type || 'credit_note' === $template_type ) {

			$payment_id     = woo_invoice_pro_get_meta( $order->get_id(), '_wcp_order_payment_id', true );
			$default_width  = apply_filters( 'woo_invoice_payment_slip_width', '100' );
			$default_height = apply_filters( 'woo_invoice_payment_slip_height', '100' );

			if ( has_post_thumbnail( $payment_id ) ) {
				echo '<h4>' . woo_invoice_filter_label( 'Payment Slip', $order, $template_type ) . '</h4><br/>';// phpcs:ignore
				echo '<a target="_blank"  href="' . get_the_post_thumbnail_url( $payment_id, 'full' ) . '">' . get_the_post_thumbnail( $payment_id, array(
						$default_height,
						$default_width
					) ) . '</a>';// phpcs:ignore
			}
		}
	}


}


new Woo_Invoice_Pro_Hooks();
