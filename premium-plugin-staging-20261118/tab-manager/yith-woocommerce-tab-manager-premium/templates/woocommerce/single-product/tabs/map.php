<?php
/**
 * This template show the tab map content
 *
 * @package YITH\TabManager\Templates
 *
 * @var string  $address The address.
 * @var int     $width The width .
 * @var int     $height The height.
 * @var int     $zoom The zoom.
 * @var boolean $full_width The map is in fullwidth.
 * @var string  $extra_content the extra content
 */

if ( empty( $address ) ) {
	exit;
}

if ( $full_width ) {
	$width = 'auto';
} else {
	$width .= 'px';
}
$height .= 'px';
?>

<div class="ywtm_map_content_wrapper">
	<div class="ywtm_additional_content">
		<?php echo wp_kses_post( apply_shortcodes( $extra_content ) ); ?>
	</div>
	<div class="ywtm_google_map_content">
		<div class="ywtm_map" style="height:<?php echo esc_attr( $height ); ?>;width:<?php echo esc_attr( $width ); ?>"
			data-zoom="<?php echo esc_attr( $zoom ); ?>" data-address="<?php echo esc_attr( $address ); ?>">
		</div>
		<a class="link_google_map" target="_blank"
			href="<?php echo esc_url( add_query_arg( array( 'q' => rawurlencode( $address ) ), '//maps.google.com/' ) ); ?>"><?php esc_html_e( 'Show in Google Map', 'yith-woocommerce-tab-manager' ); ?></a>
	</div>
</div>
