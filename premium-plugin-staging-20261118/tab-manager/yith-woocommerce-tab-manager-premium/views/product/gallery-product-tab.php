<?php
/**
 * This view show the gallery tab type
 *
 * @package YITH\TabManager\Admin
 *
 * @var YITH_Tab_Manager_Obj $tab The tab.
 * @var int                  $i The loop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product_object;
$product_tabs = $product_object->get_meta( 'ywtm_product_tabs' );

$gallery      = isset( $product_tabs[ $tab->get_id() ]['value']['gallery_ids'] ) ? $product_tabs[ $tab->get_id() ] ['value']['gallery_ids'] : '';
$gallery_rows = isset( $product_tabs[ $tab->get_id() ]['value']['gallery_columns'] ) ? $product_tabs[ $tab->get_id() ] ['value']['gallery_columns'] : 2;

?>

<div class="ywtm-tab-panel-content">
	<div class="form-field">
		<label for="ywtm-product-tab-gallery-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Images', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<div class="ywtm-gallery-field-wrapper">
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-image-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][gallery_ids]",
				'type'  => 'ywtm-media-gallery',
				'value' => $gallery,
				'data'  => array(),
			),
			true
		);
		?>
		</div>
	</div>
	<div class="form-field">
		<label for="ywtm-product-tab-image-row-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Images per row', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-image-row-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][gallery_columns]",
				'type'  => 'number',
				'value' => $gallery_rows,
				'min'   => 1,
				'max'   => 4,
			),
			true
		);
		?>
	</div>
	<input type="hidden" name="ywtm_product_tabs[<?php echo esc_attr( $tab->get_id() ); ?>][type]" value="gallery_ids"/>
</div>
