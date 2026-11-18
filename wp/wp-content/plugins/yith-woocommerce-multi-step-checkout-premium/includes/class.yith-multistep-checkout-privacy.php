<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * YITH_Multistep_Checkout_Privacy Class.
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'YITH_WCMS_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

if ( ! function_exists( 'YITH_Multistep_Checkout_Privacy' ) ) {
	/**
	 * Init privacy class
	 */
	function YITH_Multistep_Checkout_Privacy() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		if ( ! class_exists( 'YITH_Multistep_Checkout_Privacy' ) ) {

			/**
			 * Class YITH_Multistep_Checkout_Privacy
			 */
			class YITH_Multistep_Checkout_Privacy extends YITH_Privacy_Plugin_Abstract {

				/**
				 * YITH_Vendors_Privacy constructor.
				 */
				public function __construct() {

					$plugin_data = get_plugin_data( YITH_WCMS_FILE );
					$plugin_name = $plugin_data['Name'];

					parent::__construct( $plugin_name );
				}

				/**
				 * Gets the message of the privacy to display.
				 * To be overloaded by the implementor.
				 *
				 * @param string $section Privacy section.
				 *
				 * @return string
				 */
				public function get_privacy_message( $section ) {
					$message = '';
					switch ( $section ) {
						case 'collect_and_store':
							$message = '<p>' . esc_html__( 'We collect information about you during the checkout process on our store.', 'yith-woocommerce-multi-step-checkout' ) . '</p>' .
										'<p>' . esc_html__( 'While you visit our site, we’ll track:', 'yith-woocommerce-multi-step-checkout' ) . '</p>' .
										'<ul>' .
										'<li>' . esc_html__( 'Customer information on checkout page: we use a cookie to save customer information on checkout page. These data are used to restore the current checkout state in case the customer abandones the payment process for a while.', 'yith-woocommerce-multi-step-checkout' ) . '</li>' .
										'<li>' . esc_html__( 'Data saved are: your name, billing address, shipping address, email address, phone number, credit card/payment type (no credit card/payment information) and other fields added by 3rd-party plugins', 'yith-woocommerce-multi-step-checkout' ) . '</li>' .
										'</ul>';
							break;

						case 'has_access':
							$message = '<p>' . esc_html__( "Members of our team haven't access to the information you provide us because this cookie are stored on your browser software.", 'yith-woocommerce-multi-step-checkout' ) . '</p><p>' . __( 'Our team members have access to this information to help fulfill orders, process refunds and support you.', 'yith-woocommerce-multi-step-checkout' ) . '</p>';
							break;
					}

					return $message;
				}
			}
		}

		return new YITH_Multistep_Checkout_Privacy();
	}
}

add_action( 'plugins_loaded', 'YITH_Multistep_Checkout_Privacy', 20 );
