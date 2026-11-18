<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly!
}

if ( ! class_exists( 'WPSC_REST_API' ) ) :

	final class WPSC_REST_API {

		/**
		 * REST API Settings
		 *
		 * @var array
		 */
		private static $settings = array();

		/**
		 * Initialize this class
		 *
		 * @return void
		 */
		public static function init() {

			// initialize rest api.
			add_filter( 'rest_authentication_errors', array( __CLASS__, 'load_current_user' ), 100 );
			add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		}

		/**
		 * Load current user for supportcandy after user authentication
		 *
		 * @param mixed $response - response object.
		 * @return mixed
		 */
		public static function load_current_user( $response ) {

			WPSC_Current_User::load_current_user();
			return $response;
		}

		/**
		 * Register REST API routes
		 *
		 * @return void
		 */
		public static function register_routes() {

			$advanced = get_option( 'wpsc-ms-advanced-settings' );
			if ( empty( $advanced['rest-api'] ) ) {
				return;
			}

			do_action( 'wpsc_rest_register_routes' );
		}

		/**
		 * Validate integer value
		 *
		 * @param string          $param - parameter value.
		 * @param WP_REST_Request $request - request object.
		 * @param string          $key - filter key.
		 * @return boolean
		 */
		public static function validate_integer_value( $param, $request, $key ) {

			return is_numeric( $param );
		}

		/**
		 * Sanitize page number. Minimum 1.
		 *
		 * @param mixed $param - param value.
		 * @return int
		 */
		public static function sanitize_page( $param ) {

			return max( 1, (int) $param );
		}

		/**
		 * Sanitize items per page. Between 1 and 100.
		 *
		 * @param mixed $param - param value.
		 * @return int
		 */
		public static function sanitize_per_page( $param ) {

			return min( 100, max( 1, (int) $param ) );
		}
	}

endif;

WPSC_REST_API::init();
