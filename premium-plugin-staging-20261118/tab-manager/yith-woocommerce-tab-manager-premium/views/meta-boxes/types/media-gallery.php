    <?php
/**
 * The media gallery list field
 *
 * @package YITH\TabManager\Metaboxes\Types
 *
 * @var array $field The field.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$image_ids        = isset( $field['value'] ) ? array_filter( explode( ',', $field['value'] ) ) : array();
$hide_empty_state = count( $image_ids ) > 0;
$data             = $field['data'] ?? array();
wp_enqueue_media(); // Late enqueue media scripts.
wp_enqueue_script( 'wp-media-utils' );
$id = $field['id'] ?? $field['name'];
?>

<div id="<?php echo esc_attr( $id ); ?>" class="ywtm-media-gallery-field-wrapper" <?php yith_plugin_fw_html_data_to_string( $data, true ); ?>>
	<div class="ywtm-media-gallery-empty-state <?php echo $hide_empty_state ? 'hide' : ''; ?>">
		<div class="ywtm-media-gallery__preview__upload">
			<?php
			printf(
			// translators: %s is the alternative action "Upload a file".
				esc_html__( 'Drag or %s', 'yith-woocommerce-tab-manager' ),
				'<a href="#" class="ywtm-media-gallery__preview__upload__link">' . esc_html__( 'upload images', 'yith-woocommerce-tab-manager' ) . '</a>'
			)
			?>
		</div>
		<div class="ywtm-media-gallery__preview__dropzone"></div>
	</div>

	<div class="ywtm-media-gallery__media_list">
		<?php

		$i = 0;
		foreach ( $image_ids as $image_id ) :
			$url = wp_get_attachment_image_url( $image_id, 'full' );
			$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			?>
			<div class="ywtm-media-gallery__single_box ywtm-media-gallery__image_preview" data-index="<?php echo esc_attr( $i ); ?>" data-image_id="<?php echo esc_attr( $image_id ); ?>">
				<img class="ywtm-media-gallery__image" alt="<?php echo esc_attr( $alt ); ?>" src="<?php echo esc_url( $url ); ?>">
					<span class="ywtm-media-gallery__delete ywtm-delete-btn yith-icon yith-icon-close-alt"></span>

			</div>
			<?php
			++$i;
		endforeach;
		?>
		<div class="ywtm-media-gallery__single_box ywtm-media-gallery__add_new <?php echo ! $hide_empty_state ? 'hide' : ''; ?>">
			<i class="yith-icon yith-icon-plus"></i>
		</div>
		<input type="hidden" class="ywtm_media_ids" name="<?php echo esc_attr( $field['name'] ); ?>"
				value="<?php echo esc_attr( $field['value'] ); ?>"/>
	</div>
</div>
<script type="text/template" id="tmpl-ywtm-new-media-gallery-image-<?php echo esc_attr( $id ); ?>">
	<div class="ywtm-media-gallery__single_box ywtm-media-gallery__image_preview" data-index="{{{data.index}}}" data-image_id="{{{data.imageID}}}">
		<img class="ywtm-media-gallery__image"  alt="{{{data.imageALT}}}" src="{{{data.imageURL}}}">
			<span class="ywtm-media-gallery__delete ywtm-delete-btn yith-icon yith-icon-close-alt"></span>
	</div>
</script>