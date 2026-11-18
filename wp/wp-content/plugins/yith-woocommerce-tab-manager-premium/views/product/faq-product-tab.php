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

$faqs = isset( $product_tabs[ $tab->get_id() ]['value']['faqs'] ) ? $product_tabs[ $tab->get_id() ] ['value']['faqs'] : '';

?>

<div class="ywtm-tab-panel-content">
	<div class="form-field">
		<label for="ywtm-product-faq-gallery-<?php echo esc_attr( $tab->get_id() ); ?>">
			<?php esc_html_e( 'FAQs', 'yith-woocommerce-tab-manager' ); ?>
		</label>
		<div class="ywtm-faq-field-wrapper">
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywtm-product-tab-faq-' . $tab->get_id(),
				'name'  => "ywtm_product_tabs[{$tab->get_id()}][value][faqs]",
				'type'  => 'ywtm-faq',
				'value' => $faqs,
				'data'  => array(),
			),
			true
		);
		?>
		</div>
	</div>
	<input type="hidden" name="ywtm_product_tabs[<?php echo esc_attr( $tab->get_id() ); ?>][type]" value="faqs"/>
</div>
