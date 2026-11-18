<?php
/**
 * This template show the tab content
 *
 * @package YITH WooCommerce Tab Manager\Templates
 *
 * @var string $default The content
 */



$use_the_content_filter = apply_filters( 'ywtm_use_default_the_content_filter', true );

if ( $use_the_content_filter ) {
	$content = apply_filters( 'the_content', $default );
} else {
	$content = apply_filters( 'ywtm_the_content', $default );
}
?>

<div class="ywtm_default_content_tab"> <?php echo do_shortcode( $content ) ; ?></div>
