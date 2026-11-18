<?php
/**
 * The contact form field
 *
 * @package YITH\TabManager\Metaboxes\Types
 *
 * @var array $field The field.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$data               = isset( $field['data'] ) ? $field['data'] : array();
$contact_form_value = $field['value'] ?? array();



$contact_form_fields = array(
	'name'    => array(
		'title'       => __( 'Name', 'yith-woocommerce-tab-manager' ),
		'onoff_field' => true,
		'default'     => array(
			'enabled'     => 'no',
			'label'       => __( 'Name', 'yith-woocommerce-tab-manager' ),
			'placeholder' => '',
			'required'    => 'no',
		),
		'elements'    => array(
			array(
				'id'    => 'label',
				'title' => __( 'Label', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'placeholder',
				'title' => __( 'Placeholder', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'required',
				'title' => __( 'Required', 'yith-woocommerce-tab-manager' ),
				'type'  => 'checkbox',
			),
		),
	),
	'email'   => array(
		'title'       => __( 'Email', 'yith-woocommerce-tab-manager' ),
		'onoff_field' => false,
		'default'     => array(
			'label'       => __( 'Email', 'yith-woocommerce-tab-manager' ),
			'placeholder' => '',
			'enabled'     => 'yes',
			'required'    => 'yes',
		),
		'elements'    => array(
			array(
				'id'    => 'label',
				'title' => __( 'Label', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'placeholder',
				'title' => __( 'Placeholder', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'required',
				'type'  => 'hidden',
				'title' => '',
			),
			array(
				'id'    => 'enabled',
				'type'  => 'hidden',
				'title' => '',
			),
		),
	),
	'webaddr' => array(
		'title'       => __( 'Website', 'yith-woocommerce-tab-manager' ),
		'onoff_field' => true,
		'default'     => array(
			'enabled'     => 'no',
			'label'       => __( 'Website', 'yith-woocommerce-tab-manager' ),
			'placeholder' => '',
			'required'    => 'no',
		),
		'elements'    => array(
			array(
				'id'    => 'label',
				'title' => __( 'Label', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'placeholder',
				'title' => __( 'Placeholder', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'required',
				'title' => __( 'Required', 'yith-woocommerce-tab-manager' ),
				'type'  => 'checkbox',
			),
		),
	),
	'subj'    => array(
		'title'       => __( 'Subject', 'yith-woocommerce-tab-manager' ),
		'onoff_field' => true,
		'default'     => array(
			'enabled'     => 'no',
			'label'       => __( 'Subject', 'yith-woocommerce-tab-manager' ),
			'placeholder' => '',
			'required'    => 'no',
		),
		'elements'    => array(
			array(
				'id'    => 'label',
				'title' => __( 'Label', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'placeholder',
				'title' => __( 'Placeholder', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'required',
				'title' => __( 'Required', 'yith-woocommerce-tab-manager' ),
				'type'  => 'checkbox',
			),
		),
	),
	'message' => array(
		'title'       => __( 'Message', 'yith-woocommerce-tab-manager' ),
		'onoff_field' => false,
		'default'     => array(
			'label'       => __( 'Message', 'yith-woocommerce-tab-manager' ),
			'placeholder' => '',
		),
		'elements'    => array(
			array(
				'id'    => 'label',
				'title' => __( 'Label', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'placeholder',
				'title' => __( 'Placeholder', 'yith-woocommerce-tab-manager' ),
				'type'  => 'text',
			),
		),
	),

);

?>
<div class="ywtm-contact-form-field-wrapper" <?php yith_plugin_fw_html_data_to_string( $data, true ); ?>>
	<?php
	foreach ( $contact_form_fields as $field_key => $contact_field ) :
		?>
		<div class="ywtm-custom-field-toggle">
			<?php
			if ( isset( $contact_form_value[ $field_key ] ) ) {
				$value = wp_parse_args(
					$contact_form_value[ $field_key ],
					$contact_field['default']
				);
			} else {
				$value = $contact_field['default'];
			}
			yith_plugin_fw_get_field(
				array(
					'id'          => "{$field['id']}__{$field_key}",
					'type'        => 'toggle-element-fixed',
					'title'       => $contact_field['title'],
					'name'        => "{$field['name']}[{$field_key}]",
					'onoff_field' => $contact_field['onoff_field'],
					'value'       => $value,
					'elements'    => $contact_field['elements'],
				),
				true,
				false
			);
			?>
		</div>
	<?php endforeach; ?>
</div>
