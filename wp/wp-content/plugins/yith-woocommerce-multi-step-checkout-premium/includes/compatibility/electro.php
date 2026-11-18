<?php
/**
 * Compatibility with Electro
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

/**
 * Checkout issue for order reviews
 */
remove_action( 'woocommerce_checkout_before_order_review', 'electro_wrap_order_review', 0 );
remove_action( 'woocommerce_checkout_after_order_review', 'electro_wrap_order_review_close', 0 );
add_action( 'yith_woocommerce_checkout_order_review', 'electro_wrap_order_review', 5 );
add_action( 'yith_woocommerce_checkout_order_review', 'electro_wrap_order_review_close', 10 );

add_action( 'wp_enqueue_scripts', 'yith_add_electro_style', 20 );

if ( ! function_exists( 'yith_add_electro_style' ) ) {
	/**
	 * Support for Electro theme
	 */
	function yith_add_electro_style() {
		$css  = 'body.yith-wcms #customer_billing_details, body.yith-wcms #customer_shipping_details, body.yith-wcms #order_review {width: 100%;}';
		$css .= ' body.yith-wcms form.woocommerce-checkout .order-review-wrapper{width: 100% !important; max-width: 100% !important;}';
		$css .= ' body.yith-wcms form.woocommerce-checkout, body.yith-wcms form.woocommerce-checkout .order-review-wrapper { margin-right: 0 !important; margin-left: 0 !important; }';

		$handle = is_rtl() ? 'electro-rtl-style' : 'electro-style';

		wp_add_inline_style( $handle, $css );
	}
}
