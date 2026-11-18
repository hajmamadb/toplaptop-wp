<?php
/**
 * Compatibility with Porto
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

add_action( 'init', 'yith_wcms_porto_support', 20 );

if ( ! function_exists( 'yith_wcms_porto_support' ) ) {
	/**
	 * Support for Porto theme
	 */
	function yith_wcms_porto_support() {
		if ( function_exists( 'porto_checkout_version' ) && porto_checkout_version() === 'v2' ) {
			remove_action( 'woocommerce_review_order_before_payment', 'porto_woocommerce_review_order_before_payment' );
		}
	}
}
