<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_Ajax' ) ) :

	final class WPSC_Ajax {

		/**
		 * Guard an ajax handler: verify nonce and capability, stop the request if either fails.
		 * Call this first thing in every ajax handler.
		 *
		 * @param string $action - nonce action; the nonce is expected in the `_ajax_nonce` request parameter.
		 * @param string $cap    - capability the current user must have.
		 * @return void
		 */
		public static function guard( $action, $cap = 'manage_options' ) {

			if ( 1 !== check_ajax_referer( $action, '_ajax_nonce', false ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => __( 'Unauthorized request!', 'supportcandy' ),
					)
				);
			}

			if ( ! current_user_can( $cap ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => __( 'Unauthorized access!', 'supportcandy' ),
					)
				);
			}
		}
	}
endif;
