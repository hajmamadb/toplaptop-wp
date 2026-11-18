<?php
/**
 * Compatibility with Astra
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

/* === Astra Theme Support === */
if ( class_exists( 'Astra_Woocommerce' ) ) {
	$Astra_Woocommerce = Astra_Woocommerce::get_instance(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	remove_action( 'wp', array( $Astra_Woocommerce, 'woocommerce_checkout' ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	add_action( 'wp_enqueue_scripts', 'yith_wcms_enqueue_scripts', 20 );
}

if ( ! function_exists( 'yith_wcms_enqueue_scripts' ) ) {
	/**
	 * Add a css style for Astra
	 */
	function yith_wcms_enqueue_scripts() {
		$style = '.woocommerce.woocommerce-checkout form #order_review, .woocommerce-page.woocommerce-checkout form #order_review, .woocommerce-page.woocommerce-checkout form #order_review_heading{width: 100%; float:none; border-width: 0; padding-left: 0; padding-right: 0;}';
		wp_add_inline_style( 'woocommerce-general', $style );
	}
}
