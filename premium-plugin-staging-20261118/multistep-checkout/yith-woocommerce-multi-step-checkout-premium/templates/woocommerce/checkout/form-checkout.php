<?php
/**
 * Checkout Form
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\MultiStepCheckout\Templates
 * @version 2.3.0
 */

/**
 * ================= *
 * Standard Step Mapping
 * before version 2.0.0
 *
 * 0 -> Login
 * 1 -> Billing
 * 2 -> Shipping
 * 3 -> Order Info
 * 4 -> Payment
 * ================= *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( YITH_Multistep_Checkout()->frontend ) ) {
	return false;
}

$enable_checkout_login_reminder = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder', 'yes' );
$show_back_to_cart_button       = 'yes' === get_option( 'yith_wcms_nav_enable_back_to_cart_button', 'yes' );
$is_user_logged_in              = is_user_logged_in();
$show_login_step                = ! $is_user_logged_in && $enable_checkout_login_reminder;

/**
 * APPLY_FILTERS: yith_wcms_step_button_class
 *
 * Filters the CSS classes for the previous/next step button in the checkount.
 *
 * @param string $classes CSS classes.
 *
 * @return string
 */
$button_class = apply_filters( 'yith_wcms_step_button_class', 'button alt yith-wcms-button' );

/**
 * APPLY_FILTERS: yith_wcms_timeline_labels
 *
 * Filters the labels to use in the checkout timeline.
 *
 * @param array $labels Array of labels.
 *
 * @return array
 */
$labels = apply_filters(
	'yith_wcms_timeline_labels',
	array(
		'next'         => esc_html__( 'Next', 'yith-woocommerce-multi-step-checkout' ),
		'skip_login'   => esc_html__( 'Next', 'yith-woocommerce-multi-step-checkout' ),
		'prev'         => esc_html__( 'Previous', 'yith-woocommerce-multi-step-checkout' ),
		'login'        => esc_html_x( 'Login', 'Checkout: user timeline', 'yith-woocommerce-multi-step-checkout' ),
		'billing'      => esc_html_x( 'Billing', 'Checkout: user timeline', 'yith-woocommerce-multi-step-checkout' ),
		'shipping'     => esc_html_x( 'Shipping', 'Checkout: user timeline', 'yith-woocommerce-multi-step-checkout' ),
		'order'        => esc_html_x( 'Order Info', 'Checkout: user timeline', 'yith-woocommerce-multi-step-checkout' ),
		'payment'      => esc_html_x( 'Payment Info', 'Checkout: user timeline', 'yith-woocommerce-multi-step-checkout' ),
		'back_to_cart' => esc_html_x( 'Back to cart', 'Frontend: button label', 'yith-woocommerce-multi-step-checkout' ),
	)
);

/**
 * APPLY_FILTERS: yith_wcms_timeline_display
 *
 * Filters the timeline display mode in the checkout.
 *
 * @param string $display_mode Timeline display mode.
 *
 * @return string
 */
$display                  = apply_filters( 'yith_wcms_timeline_display', get_option( 'yith_wcms_timeline_display', 'horizontal' ) );
$merge_billing_shipping   = 'yes' === get_option( 'yith_wcms_timeline_options_merge_billing_and_shipping_step', 'no' );
$merge_order_info_payment = 'yes' === get_option( 'yith_wcms_timeline_options_merge_order_and_payment_step', 'no' );
$checkout_style           = YITH_Multistep_Checkout()->frontend->get_checkout_style();

$timeline_args = array(
	'labels'                   => $labels,
	'is_user_logged_in'        => $is_user_logged_in,
	'display'                  => $display,
	'style'                    => $checkout_style,
	'shipping_step_enabled'    => YITH_Multistep_Checkout()->frontend->is_step_enabled( 'shipping' ),
	'payment_step_enabled'     => YITH_Multistep_Checkout()->frontend->is_step_enabled( 'payment' ),
	'merge_billing_shipping'   => $merge_billing_shipping,
	'merge_order_info_payment' => $merge_order_info_payment,
);

$enable_nav_button = 'yes' === get_option( 'yith_wcms_nav_buttons_enabled', 'yes' );

$enable_checkout_login_reminder = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder', 'yes' );

$default_login_message = esc_html_x( 'If you have already registered, please, enter your details below. If you are a new customer, please, go to the Billing &amp; Shipping section.', '[Frontend] Default message for returning customer on checkout page', 'yith-woocommerce-multi-step-checkout' );

/**
 * APPLY_FILTERS: yith_wcms_form_checkout_login_message
 *
 * Filters the message to login before proceeding with the checkout.
 *
 * @param string $login_message Login message.
 *
 * @return string
 */
$login_message = apply_filters( 'yith_wcms_form_checkout_login_message', get_option( 'yith_wcms_form_checkout_login_message', $default_login_message ) );

// load timeline template.
function_exists( 'yith_wcms_load_checkout_timeline' ) && yith_wcms_load_checkout_timeline( $timeline_args );

do_action( 'woocommerce_before_checkout_form', $checkout );
?>

	<div id="checkout-wrapper" class="timeline-<?php echo esc_attr( $display . ' ' . $checkout_style ); ?>">
		<div id="checkout_coupon" class="woocommerce_checkout_coupon">
			<?php
			/**
			 * DO_ACTION: yith_woocommerce_checkout_coupon
			 *
			 * Allows to render some content in the coupon form in the checkout.
			 *
			 * @param WC_Checkout $checkout Checkout object.
			 */
			do_action( 'yith_woocommerce_checkout_coupon', $checkout );
			?>
		</div>
		<?php if ( $enable_checkout_login_reminder ) : ?>
			<div id="checkout_login" class="woocommerce_checkout_login">
				<?php
				/**
				 * DO_ACTION: yith_wcms_checkout_login_form
				 *
				 * Allows to render some content in the login form in the checkout.
				 *
				 * @param string $login_message Login message.
				 */
				do_action( 'yith_wcms_checkout_login_form', $login_message );

				?>
			</div>
		<?php endif; ?>

		<?php

		// If checkout registration is disabled and not logged in, the user cannot checkout.
		if ( ! $checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
			echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in before proceeding to checkout.', 'yith-woocommerce-multi-step-checkout' ) ) );
			return;
		}

		// filter hook for include new pages inside the payment method.
		$get_checkout_url = apply_filters( 'woocommerce_get_checkout_url', yith_get_checkout_url() );

		?>

		<form name="checkout" method="post" class="checkout woocommerce-checkout processing" action="<?php echo esc_url( $get_checkout_url ); ?>" enctype="multipart/form-data">

			<?php if ( count( $checkout->checkout_fields ) > 0 ) : ?>

				<div class="checkout_billing
				<?php
				echo $is_user_logged_in ? 'logged-in' : 'not-logged-in';
				echo $enable_checkout_login_reminder ? ' show-login-reminder' : ' hide-login-reminder';
				?>
				" id="customer_billing_details">
					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
					<?php
					if ( $merge_billing_shipping ) {
						do_action( 'woocommerce_before_checkout_shipping_form', $checkout );
						do_action( 'woocommerce_checkout_shipping' );
						do_action( 'woocommerce_checkout_after_customer_details' );
					}
					?>
				</div>
				<?php if ( YITH_Multistep_Checkout()->frontend->is_step_enabled( 'shipping' ) ) : ?>
					<div class="checkout_shipping" id="customer_shipping_details">
						<?php
						do_action( 'woocommerce_before_checkout_shipping_form', $checkout );
						do_action( 'woocommerce_checkout_shipping' );
						do_action( 'woocommerce_checkout_after_customer_details' );
						?>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<?php do_action( 'woocommerce_checkout_before_order_review', $checkout ); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<div id="order_info" class="woocommerce-checkout-review-order">
					<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'yith-woocommerce-multi-step-checkout' ); ?></h3>
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
					<?php
					/**
					 * DO_ACTION: yith_woocommerce_checkout_order_review
					 *
					 * Allows to render some content in the order review section in the checkout.
					 */
					do_action( 'yith_woocommerce_checkout_order_review' );
					?>
					<?php
					if ( $merge_order_info_payment ) {
						/**
						 * DO_ACTION: yith_woocommerce_checkout_payment
						 *
						 * Allows to render some content in the payment section in the checkout.
						 */
						do_action( 'yith_woocommerce_checkout_payment' );
					}
					?>
				</div>

				<?php if ( YITH_Multistep_Checkout()->frontend->is_step_enabled( 'payment' ) ) : ?>
					<div id="order_checkout_payment" class="yith-woocommerce-checkout-payment">
						<h3 id="payment_heading">
							<?php
							/**
							 * APPLY_FILTERS: yith_wcms_payment_step_section_title
							 *
							 * Filters the title of the section in the Payment step.
							 *
							 * @param string $section_title Section title.
							 *
							 * @return string
							 */
							echo esc_html( apply_filters( 'yith_wcms_payment_step_section_title', __( 'Payment', 'yith-woocommerce-multi-step-checkout' ) ) );
							?>
						</h3>
						<?php do_action( 'yith_woocommerce_checkout_payment' ); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

		</form>

		<div id="form_actions" data-step="<?php echo $show_login_step ? 'login' : 'billing'; ?>">
			<?php $button_type = $enable_nav_button ? 'button' : 'hidden'; ?>
			<input type="<?php echo esc_attr( $button_type ); ?>" class="<?php echo esc_attr( $button_class ); ?> prev" name="checkout_prev_step" value="<?php echo esc_attr( $labels['prev'] ); ?>" data-action="prev">
			<span></span>
			<input type="<?php echo esc_attr( $button_type ); ?>" class="<?php echo esc_attr( $button_class ); ?> next" name="checkout_next_step" value="<?php echo esc_attr( $enable_checkout_login_reminder && ! is_user_logged_in() ? $labels['skip_login'] : $labels['next'] ); ?>" data-action="next">
			<?php if ( $show_back_to_cart_button ) : ?>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" id="yith-wcms-back-to-cart-button" class="<?php echo esc_attr( $button_class ); ?>"><?php echo esc_html( $labels['back_to_cart'] ); ?></a>
			<?php endif; ?>
		</div>
	</div>

<?php
do_action( 'woocommerce_after_checkout_form', $checkout );
