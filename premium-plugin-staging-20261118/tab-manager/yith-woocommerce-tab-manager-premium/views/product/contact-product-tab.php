<?php
/**
 * This view show the form tab type
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

$value = isset( $product_tabs[ $tab->get_id() ]['value']['form'] ) ? $product_tabs[ $tab->get_id() ] ['value']['form'] : '';

?>

<div class="ywtm-tab-panel-content">
	<div class="form-field ywtm-modal-editor">
		<label for="_ywtm_default_<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'Contact form', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-form-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][form]",
				'type'  => 'ywtm-contact-form',
				'value' => $value,
				'data'  => array(),
			),
			true
		);
		?>
		<input type="hidden" name="ywtm_product_tabs[<?php echo esc_attr( $tab->get_id() ); ?>][type]" value="form">
	</div>
</div>
