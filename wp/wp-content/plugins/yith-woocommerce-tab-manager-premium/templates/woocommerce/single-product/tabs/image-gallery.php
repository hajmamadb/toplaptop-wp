<?php
/**
 * This template show the tab gallery content
 *
 * @package YITH\TabManager\Templates
 *
 * @var int    $tab_id the tab id.
 * @var array  $image_ids The image ids.
 * @var string $columns the image for row.
 * @var string $extra_content The extra content to show before.
 */

wp_enqueue_script( 'prettyPhoto' );
wp_enqueue_script( 'prettyPhoto-init' );
wp_enqueue_style( 'woocommerce_prettyPhoto_css' );

if ( ! isset( $image_ids ) ) {
	exit;
}

$thumbnail_size  = apply_filters( 'yith_tab_manager_image_thumbnail_size', 'woocommerce_thumbnail' );
$image_full_size = apply_filters( 'yith_tab_manager_image_full_size', 'full' );
?>

<div class="ywtm_image_gallery_content_wrapper">
	<div class="ywtm_additional_content">
		<?php echo wp_kses_post( apply_shortcodes( $extra_content ) ); ?>
	</div>
	<div class="ywtm_image_gallery">
		<ul class="ywtm_image_gallery_list products columns-<?php echo esc_attr( $columns ); ?>">
			<?php
			foreach ( $image_ids as $image_id ) :
				$img_src_thumbn = wp_get_attachment_image( $image_id, $thumbnail_size );
				$image_title    = get_the_title( $image_id );
				$img_src_full   = wp_get_attachment_image_src( $image_id, $image_full_size );
				$img_src_full   = is_array( $img_src_full ) ? $img_src_full[0] : $img_src_full;
				?>
				<li class="product ywtm_gallery_item">
					<a href="<?php echo esc_url( $img_src_full ); ?>"
						title="<?php echo esc_attr( $image_title ); ?>"
						data-rel="ywtmPrettyPhoto[gallery-<?php echo esc_attr( $tab_id ); ?>]">
						<?php echo wp_kses_post( $img_src_thumbn ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

