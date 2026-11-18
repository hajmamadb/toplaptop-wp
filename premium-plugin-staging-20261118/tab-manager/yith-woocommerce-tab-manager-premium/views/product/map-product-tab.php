<?php
/**
 * This view show the map tab type
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

$full_width = isset( $product_tabs[ $tab->get_id() ]['value']['map_full_width'] ) ? $product_tabs[ $tab->get_id() ] ['value']['map_full_width'] : 'no';
$width      = isset( $product_tabs[ $tab->get_id() ]['value']['map_width'] ) ? $product_tabs[ $tab->get_id() ] ['value']['map_width'] : 100;
$height     = isset( $product_tabs[ $tab->get_id() ]['value']['map_height'] ) ? $product_tabs[ $tab->get_id() ] ['value']['map_height'] : 100;
$address    = isset( $product_tabs[ $tab->get_id() ]['value']['map_overlay_address'] ) ? $product_tabs[ $tab->get_id() ] ['value']['map_overlay_address'] : '';
$zoom       = isset( $product_tabs[ $tab->get_id() ]['value']['map_overlay_zoom'] ) ? $product_tabs[ $tab->get_id() ] ['value']['map_overlay_zoom'] : 15;

?>

<div class="ywtm-tab-panel-content">
	<div class="form-field">
		<label for="ywtm-product-tab-address-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Address', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<div class="ywtm-map-field-wrapper">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'    => 'ywtm-product-tab-map-address-' . $tab->get_id(),
					'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][map_overlay_address]",
					'type'  => 'text',
					'value' => $address,
					'data'  => array(),
				),
				true
			);
			?>
		</div>
	</div>
	<div class="form-field map-is-full">
		<label for="ywtm-product-tab-map-is-full-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Full-width map', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-map-full-width-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][map_full_width]",
				'type'  => 'onoff',
				'value' => $full_width,
			),
			true
		);
		?>
	</div>
	<div class="form-field map-width">
		<label for="ywtm-product-tab-map-width-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Width (px)', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-map-width-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][map_width]",
				'type'  => 'number',
				'value' => $width,
				'min'   => 1,
			),
			true
		);
		?>
	</div>
	<div class="form-field">
		<label for="ywtm-product-tab-map-height-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Height (px)', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-map-height-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][map_height]",
				'type'  => 'number',
				'value' => $height,
				'min'   => 1,
			),
			true
		);
		?>
	</div>
	<div class="form-field">
		<label for="ywtm-product-tab-map-zoom-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Map zoom', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-map-zoom-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][map_overlay_zoom]",
				'type'  => 'number',
				'value' => $zoom,
				'min'   => 0,
				'max'   => 19,
			),
			true
		);
		?>
	</div>
	<input type="hidden" name="ywtm_product_tabs[<?php echo esc_attr( $tab->get_id() ); ?>][type]"
			value="map_overlay_address"/>
</div>
