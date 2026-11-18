<?php
/**
 * HTML Template Email Funds
 *
 * @var string   $email_heading The heading.
 * @var WC_Email $email The email.
 * @var bool     $sent_to_admin Is this sent to admin?
 * @var bool     $plain_text Is this plain?
 * @var string   $custom_message The email message.
 *
 * @package YITH\TabManager\Templates\Emails
 * @since   1.0.0
 * @author YITH <plugins@yithemes.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product       = $email->object['info']['product'];
$details       = $email->object['info']['details'];
$customer_keys = array(
	'webaddr' => __( 'Website:', 'yith-woocommerce-tab-manager' ),
	'name'    => __( 'Name:', 'yith-woocommerce-tab-manager' ),
	'message' => __( 'Message:', 'yith-woocommerce-tab-manager' ),
	'email'   => __( 'Email:', 'yith-woocommerce-tab-manager' ),
);
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<?php echo wp_kses_post( wpautop( wptexturize( $custom_message ) ) ); ?>
	<div class="ywtm_ask_info_email_product">
		<div class="ywtm_ask_info_product_image">
			<?php echo $product->get_image( 'full' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<div class="ywtm_ask_info_product_link">
			<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</div>
	</div>
	<div class="ywtm_ask_info_user_details_wrapper">
		<h3><?php esc_html_e( 'Customer details', 'yith-woocommerce-tab-manager' ); ?></h3>
		<ul class="ywtm_ask_info_user_details">
			<?php
			foreach ( $details as $user_key => $user_value ) {

				if ( ! empty( $user_value ) && isset( $customer_keys[ $user_key ] ) ) {
					$li = sprintf( '<li><strong>%s</strong> %s', $customer_keys[ $user_key ], $user_value );
					echo $li; //phpcs:ignore WordPress.Security.EscapeOutput
				}
			}
			?>

		</ul>
	</div>
<?php do_action( 'woocommerce_email_footer', $email ); ?>