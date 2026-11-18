<?php
/**
 * The icon list field
 *
 * @package YITH\TabManager\Metaboxes\Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( isset( $field['label'] ) ) {
	$field['title'] = $field['label'];
}

$default_field = array(
	'id'    => '',
	'title' => isset( $field['name'] ) ? $field['name'] : '',
	'desc'  => '',
);
$field         = wp_parse_args( $field, $default_field );
?>


<div id="<?php echo $field['id']; ?>-container" <?php echo yith_field_deps_data( $field ); //phpcs:ignore WordPress.Security.EscapeOutput ?>
	class="yith-plugin-fw-metabox-field-row">

	<?php
	// get the select field to choose how method use.

	$select_field = array(
		'id'      => $field['id'] . '_select_icon_mode',
		'name'    => 'yit_metaboxes[' . $field['id'] . '][select]',
		'value'   => isset( $field['value']['select'] ) ? $field['value']['select'] : 'icon',
		'type'    => 'select',
		'class'   => 'yith-plugin-fw-select ywtm_icon_list_type wc-enhanced-select',
		'options' => array(
			'icon'   => __( 'Icon', 'yith-woocommerce-tab-manager' ),
			'upload' => __( 'Custom Icon', 'yith-woocommerce-tab-manager' ),
			'none'   => __( 'None', 'yith-woocommerce-tab-manager' ),
		),
	);
	yith_plugin_fw_get_field( $select_field, true );

	// get the icon field to show the icon list.

	$icon_field = array(
		'type'         => 'icons',
		'id'           => $field['id'] . '_icon',
		'name'         => 'yit_metaboxes[' . $field['id'] . '][icon]',
		'value'        => ywtm_map_old_icon_with_new( $field['value']['icon'] ),
		'filter_icons' => YWTM_SLUG,
	);
	yith_plugin_fw_get_field( $icon_field, true );


	// get the upload button to custom icon.
	$button_field = array(
		'type'  => 'media',
		'id'    => $field['id'] . '_custom',
		'name'  => 'yit_metaboxes[' . $field['id'] . '][custom]',
		'value' => isset( $field['value']['custom'] ) ? $field['value']['custom'] : '',

	);
	yith_plugin_fw_get_field( $button_field, true );

	?>
</div>
