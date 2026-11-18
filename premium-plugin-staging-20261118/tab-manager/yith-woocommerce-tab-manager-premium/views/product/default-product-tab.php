<?php
/**
 * This view show the default tab type
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

$value = isset( $product_tabs[ $tab->get_id() ]['value']['content'] ) ? $product_tabs[ $tab->get_id() ] ['value']['content'] : '';

?>

<div class="ywtm-tab-panel-content">
	<div class="form-field ywtm-modal-editor yith-plugin-fw--required">
		<label for="ywtm-product-tab-editor-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Tab content', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-editor-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][content]",
				'type'  => 'ywtm-modal-editor',
				'value' => $value,
				'data'  => array(),
			),
			true
		);
		?>
		<input type="hidden" name="ywtm_product_tabs[<?php echo esc_attr( $tab->get_id() ); ?>][type]" value="content">
	</div>
</div>
