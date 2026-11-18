<?php
/**
 * Template for manage woocommerce tabs
 *
 * @author YITH <plugins@yithemes.com>
 * @since 1.1.0
 * @package YITH\TabManager\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * The product object
 *
 * @var WC_Product
 */
global $product_object;
$product_id = $product_object->get_id();


$wc_tabs = yith_tab_manager_get_default_tab( $product_object );


?>

<div id="ywtm_wc_tab" class="panel woocommerce_options_panel hidden">
	<div class="ywtm-product-edit-tabs-container">
		<div class="ywtm-product-edit-tab-head">
			<h4><?php esc_html_e( 'Default WooCommerce Tabs', 'yith-woocommerce-tab-manager' ); ?></h4>
			<span><?php esc_html_e( 'Hide or customize the default WooCommerce tabs for this product.', 'yith-woocommerce-tab-manager' ); ?></span>
		</div>
	</div>
	<div class="ywtm-product-edit-tabs-list ywtm-product-edit-tabs-container">
		<?php
		foreach ( $wc_tabs as $tab_key => $wc_tab ) :
			$override_meta_key = 'ywtm_override_' . $tab_key;
			$hide_meta_key     = 'ywtm_hide_' . $tab_key;
			$title_meta_key    = 'ywtm_title_tab_' . $tab_key;
			$content_meta_key  = 'ywtm_content_tab_' . $tab_key;
			$override          = $product_object->get_meta( '_' . $override_meta_key );
			$hide              = $product_object->get_meta( '_' . $hide_meta_key );
			$custom_title      = $product_object->get_meta( '_' . $title_meta_key );
			$custom_content    = $product_object->get_meta( '_' . $content_meta_key );
			$custom_title      = empty( $custom_title ) ? esc_html( $wc_tab['title'] ) : esc_html( $custom_title );
			if ( 'reviews' === $tab_key ) {
				$wc_title = sprintf( $custom_title, $product_object->get_review_count() );
			} else {
				$wc_title = $custom_title;
			}
			?>
			<div class="ywtm-product-edit-tab ywtm-<?php echo esc_attr( $tab_key ); ?> <?php echo $wc_tab['disabled'] ? 'disabled' : ''; ?>">
				<div class="ywtm-product-edit-tab--header">
					<div class="ywtm-product-edit-tab-title">
						<?php echo esc_html( $wc_title ); ?>
					</div>
					<div class="ywtm-product-edit-tab-options">
						<div class="ywtm-product-edit-tab-override">
							<label for="<?php echo esc_attr( $override_meta_key ); ?>"><?php esc_html_e( 'Override', 'yith-woocommerce-tab-manager' ); ?></label>
							<?php
							yith_plugin_fw_get_field(
								array(
									'id'    => $override_meta_key,
									'name'  => $override_meta_key,
									'type'  => 'checkbox',
									'value' => $override,
								),
								true,
								false
							);
							?>
						</div>
						<div class="ywtm-product-edit-tab-hide">
							<label for="<?php echo esc_attr( $hide_meta_key ); ?>"><?php esc_html_e( 'Hide', 'yith-woocommerce-tab-manager' ); ?></label>
							<?php
							yith_plugin_fw_get_field(
								array(
									'id'    => $hide_meta_key,
									'name'  => $hide_meta_key,
									'type'  => 'checkbox',
									'value' => $hide,
								),
								true,
								false
							);
							?>
						</div>
					</div>
				</div>
				<div class="ywtm-product-edit-tab-fields-wrapper">
					<div class="ywtm-product-edit-tab-fields">
						<div class="ywtm-product-edit-tab-field-title">
							<label for="<?php echo esc_attr( $title_meta_key ); ?>"><?php esc_html_e( 'Title', 'yith-woocommerce-tab-manager' ); ?></label>
							<?php
							yith_plugin_fw_get_field(
								array(
									'id'    => $title_meta_key,
									'name'  => $title_meta_key,
									'type'  => 'text',
									'value' => $custom_title,
								),
								true,
								false
							);
							?>
						</div>
						<div class="ywtm-product-edit-tab-field-content">
							<label for="<?php echo esc_attr( $content_meta_key ); ?>"><?php esc_html_e( 'Content', 'yith-woocommerce-tab-manager' ); ?></label>
							<?php
							yith_plugin_fw_get_field(
								array(
									'id'            => $content_meta_key,
									'name'          => $content_meta_key,
									'type'          => 'textarea-editor',
									'value'         => $custom_content,
									'textarea_rows' => 25,
								),
								true,
								false
							);
							?>
							<div class="description"><?php esc_html_e( 'Leave empty to show the default content.', 'yith-woocommerce-tab-manager' ); ?></div>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
