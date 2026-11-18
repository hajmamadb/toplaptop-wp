<?php
/**
 * This class manage all ajax call
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Ajax' ) ) {

	/**
	 * This class manage all AJAX action
	 */
	class YITH_Tab_Manager_Ajax {
		use YITH_Tab_Manager_Trait_Singleton;

		/**
		 * The construct
		 */
		protected function __construct() {
			// ADMIN call.
			add_action( 'wp_ajax_ywtm_toggle_show_tab', array( $this, 'toggle_show_tab' ) );
			add_action( 'wp_ajax_ywtm_sort_tabs', array( $this, 'sort_tabs' ) );

			// Frontend call.
			add_action( 'wp_ajax_nopriv_ywtm_send_form', array( $this, 'send_form' ) );
			add_action( 'wp_ajax_ywtm_send_form', array( $this, 'send_form' ) );
		}

		/**
		 * Enable/Disable the tab
		 *
		 * @return void
		 */
		public function toggle_show_tab() {
			check_ajax_referer( 'toggle-show-tab', 'security' );

			if ( isset( $_REQUEST['tab_id'] ) ) {
				$tab_id = sanitize_text_field( wp_unslash( $_REQUEST['tab_id'] ) );
				$active = isset( $_REQUEST['enabled'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['enabled'] ) ) : 'no';
				try {
					$tab = ywtm_get_tab( $tab_id );
					$tab->set_active( $active );
					$tab->save();
					wp_send_json_success();
				} catch ( Exception $e ) {
					wp_send_json_error( $e->getMessage() );
				}
			}
		}

		/**
		 * Sort the tabs
		 *
		 * @return void
		 */
		public function sort_tabs() {
			check_ajax_referer( 'sort-tabs', 'security' );

			if ( isset( $_REQUEST['tabs_to_sort'] ) ) {
				$tabs_to_sort = explode( ',', sanitize_text_field( wp_unslash( $_REQUEST['tabs_to_sort'] ) ) );
				$old_priority = array();

				foreach ( $tabs_to_sort as $tab_id ) {
					$tab            = ywtm_get_tab( $tab_id );
					$old_priority[] = $tab->get_order();
				}
				asort( $old_priority );

				$sorted_priority = array_values( $old_priority );

				$i = 0;
				foreach ( $tabs_to_sort as $tab_id ) {
					$tab          = ywtm_get_tab( $tab_id );
					$new_priority = $sorted_priority[ $i ];
					$tab->set_order( $new_priority );
					$tab->save();
					++$i;
				}

				wp_send_json_success();
			}
		}

		/**
		 * Try to send the email to admin
		 *
		 * @return void
		 */
		public function send_form() {
			check_ajax_referer( 'send-form', 'security' );

			$form = isset( $_REQUEST['form'] ) ? wp_unslash( $_REQUEST['form'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( $form ) {

				$details = array();
				foreach ( $form as $key => $value ) {
					if ( 'ywtm_product_id' !== $key ) {
						$details[ str_replace( 'ywtm_', '', $key ) ] = sanitize_text_field( $value );
					}
				}
				$args = array(
					'info' => array(
						'product' => wc_get_product( $form['ywtm_product_id'] ),
						'details' => $details,
					),
				);

				WC()->mailer();
				do_action( 'send_tab_manager_email_notification', $args );
			} else {
				$error = yith_plugin_fw_get_component(
					array(
						'type'        => 'notice',
						'dismissible' => false,
						'notice_type' => 'error',
						'message'     => __( 'An error occurred while submitting the form', 'yith-woocommerce-tab-manager' ),
					),
					false
				);
				wp_send_json_error( $error );
			}
		}
	}
}
