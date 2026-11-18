<?php
/**
 * Thankyou page
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\MultiStepCheckout\Templates
 * @version 2.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $order instanceof WC_Order ) : ?>

	<?php if ( $order->has_status( 'failed' ) ) : ?>

		<p><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'yith-woocommerce-multi-step-checkout' ); ?></p>

		<p>
			<?php
			if ( is_user_logged_in() ) {
				esc_html_e( 'Please, either submit your order again or go to your account page.', 'yith-woocommerce-multi-step-checkout' );
			} else {
				esc_html_e( 'Please submit your order again.', 'yith-woocommerce-multi-step-checkout' );
			}
			?>
		</p>

		<p>
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'yith-woocommerce-multi-step-checkout' ); ?></a>
			<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My Account', 'yith-woocommerce-multi-step-checkout' ); ?></a>
			<?php endif; ?>
		</p>

	<?php else : ?>
		<span class="yith-wcms-title">
			<h1 class="endpoint-title"><?php echo wp_kses_post( WC()->query->get_endpoint_title( 'order-received' ) ); ?></h1>
			<p><?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. We have received your order.', 'yith-woocommerce-multi-step-checkout' ), $order ) ); ?></p>
		</span>

		<ul class="order_details yith-order-info">
			<li class="order">
				<?php esc_html_e( 'Order number:', 'yith-woocommerce-multi-step-checkout' ); ?>
				<strong><?php echo esc_html( $order->get_order_number() ); ?></strong>
			</li>
			<li class="date">
				<?php esc_html_e( 'Date:', 'yith-woocommerce-multi-step-checkout' ); ?>
				<strong><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ) ); ?></strong>
			</li>
			<li class="total">
				<?php esc_html_e( 'Total:', 'yith-woocommerce-multi-step-checkout' ); ?>
				<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
			</li>

			<?php
			$payment_method_title = $order->get_payment_method_title();

			if ( $payment_method_title ) :
				?>
				<li class="method">
					<?php esc_html_e( 'Payment method:', 'yith-woocommerce-multi-step-checkout' ); ?>
					<strong><?php echo esc_html( $payment_method_title ); ?></strong>
				</li>
			<?php endif; ?>
		</ul>
		<div class="clear"></div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
	<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

<?php else : ?>

	<p><?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. We have received your order.', 'yith-woocommerce-multi-step-checkout' ), null ) ); ?></p>

<?php endif; ?>
