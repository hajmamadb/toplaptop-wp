<?php
/**
 * Compatibility with North WP
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

/* === North WP Theme Support === */
remove_action( 'woocommerce_checkout_before_customer_details', 'thb_checkout_before_customer_details', 5 );
remove_action( 'woocommerce_checkout_after_customer_details', 'thb_checkout_after_customer_details', 30 );
remove_action( 'woocommerce_checkout_after_order_review', 'thb_checkout_after_order_review', 30 );

if ( ! function_exists( 'yith_wcms_enqueue_scripts' ) ) {
	/**
	 * Add a css style for North WP
	 */
	function yith_wcms_enqueue_scripts() {
		$style = '#order_checkout_payment #place_order{ margin-bottom: 20px;}';
		wp_add_inline_style( 'thb-app', $style );
	}
}

add_action( 'wp_enqueue_scripts', 'yith_wcms_enqueue_scripts', 30 );
