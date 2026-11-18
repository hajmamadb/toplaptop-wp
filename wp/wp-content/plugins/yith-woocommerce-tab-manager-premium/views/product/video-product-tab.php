<?php
/**
 * This view show the video tab type
 *
 * @package YITH\TabManager\Admin
 *
 * @var YITH_Tab_Manager_Obj $tab The tab.
 * @var int                  $i The loop.
 * @var array                $product_tabs The tabs for this product.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product_object;
$product_tabs = $product_object->get_meta( 'ywtm_product_tabs' );

$video      = isset( $product_tabs[ $tab->get_id() ]['value']['video'] ) ? $product_tabs[ $tab->get_id() ] ['value']['video'] : '';
$video_rows = isset( $product_tabs[ $tab->get_id() ]['value']['video_rows'] ) ? $product_tabs[ $tab->get_id() ] ['value']['video_rows'] : 2;

?>

<div class="ywtm-tab-panel-content">
	<div class="form-field">
		<label for="ywtm-product-tab-video-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Videos', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<div class="ywtm-videos-field-wrapper">
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-video-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][video]",
				'type'  => 'ywtm-video',
				'value' => $video,
				'data'  => array(),
			),
			true
		);
		?>
		</div>
	</div>
	<div class="form-field">
		<label for="ywtm-product-tab-video-row-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Videos per row', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-video-row-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][video_rows]",
				'type'  => 'number',
				'value' => $video_rows,
				'min'   => 1,
				'max'   => 4,
			),
			true
		);
		?>
	</div>
	<input type="hidden" name="ywtm_product_tabs[<?php echo esc_attr( $tab->get_id() ); ?>][type]" value="video"/>
</div>
