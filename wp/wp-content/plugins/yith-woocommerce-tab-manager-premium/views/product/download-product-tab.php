<?php
/**
 * This view show the faq tab type
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

$downloads = isset( $product_tabs[ $tab->get_id() ]['value']['download'] ) ? $product_tabs[ $tab->get_id() ] ['value']['download'] : '';

?>

<div class="ywtm-tab-panel-content">
	<div class="form-field">
		<label for="ywtm-product-download-gallery-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Download', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<div class="ywtm-downloads-field-wrapper">
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-download-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][download]",
				'type'  => 'ywtm-download',
				'value' => $downloads,
				'data'  => array(),
			),
			true
		);
		?>
		</div>
	</div>
	<input type="hidden" name="ywtm_product_tabs[<?php echo esc_attr( $tab->get_id() ); ?>][type]" value="download"/>
</div>
