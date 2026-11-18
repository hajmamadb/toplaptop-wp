<?php
/**
 * Compatibility with Medizin
 *
 * @package YITH\MultiStepCheckout\Includes
 * @author  YITH <plugins@yithemes.com>
 */

/* === Medizin Theme Support === */
if ( class_exists( 'Medizin_Woo' ) ) {
	remove_action(
		'woocommerce_checkout_after_order_review',
		array(
			Medizin_Woo::instance(),
			'template_checkout_payment_title',
		),
		10
	);
	remove_action( 'woocommerce_checkout_after_order_review', 'woocommerce_checkout_payment', 20 );
}
