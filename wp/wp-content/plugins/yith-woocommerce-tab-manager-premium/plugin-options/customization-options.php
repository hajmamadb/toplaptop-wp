<?php
/**
 * The file that contain the plugin options
 *
 * @package YITH\TabManager\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'customization' => array(
		'faq-section-start'       => array(
			'name' => __( 'FAQ tab', 'yith-woocommerce-tab-manager' ),
			'type' => 'title',
		),
		'faq-icon-position'       => array(
			'id'        => 'ywtm_faq_icon_position',
			'name'      => __( 'Icon position', 'yith-woocommerce-tab-manager' ),
			'type'      => 'yith-field',
			'class'     => 'wc-enhanced-select',
			'yith-type' => 'select',
			'options'   => array(
				'left'  => __( 'Left', 'yith-woocommerce-tab-manager' ),
				'right' => __( 'Right', 'yith-woocommerce-tab-manager' ),
			),
			'default'   => 'left',
		),
		'faq-colors-default'      => array(
			'id'           => 'ywtm_faq_colors_default',
			'name'         => __( 'Colors - Default state', 'yith-woocommerce-tab-manager' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					array(
						'id'      => 'background',
						'name'    => __( 'Background', 'yith-woocommerce-tab-manager' ),
						'default' => '#f8f8f8',
					),
					array(
						'id'      => 'text',
						'name'    => __( 'Text', 'yith-woocommerce-tab-manager' ),
						'default' => '#4a5567',
					),
				),
				array(
					array(
						'id'      => 'border',
						'name'    => __( 'Borders', 'yith-woocommerce-tab-manager' ),
						'default' => '#c7c7c7',
					),
				),
			),

		),
		'faq-colors-active'       => array(
			'id'           => 'ywtm_faq_colors_active',
			'name'         => __( 'Colors - Active state', 'yith-woocommerce-tab-manager' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					array(
						'id'      => 'background',
						'name'    => __( 'Background', 'yith-woocommerce-tab-manager' ),
						'default' => '#EEF5FC',
					),
					array(
						'id'      => 'text',
						'name'    => __( 'Text', 'yith-woocommerce-tab-manager' ),
						'default' => '#2c78a3',
					),
				),
				array(
					array(
						'id'      => 'border',
						'name'    => __( 'Borders', 'yith-woocommerce-tab-manager' ),
						'default' => '#aeccdd',
					),
					array(
						'id'      => 'answer_text',
						'name'    => __( 'Answer Text', 'yith-woocommerce-tab-manager' ),
						'default' => '#4A5567',
					),
				),
			),
		),
		'faq-section-end'         => array(
			'type' => 'sectionend',
		),
		'contact-section-start'   => array(
			'name' => __( 'Contact tab', 'yith-woocommerce-tab-manager' ),
			'type' => 'title',
		),
		'contact-recipient'       => array(
			'id'        => 'ywtm_contact_recipient',
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'name'      => __( 'Send emails to:', 'yith-woocommerce-tab-manager' ),
			'default'   => get_option( 'admin_email' ),
			'desc'      => __( 'Set the email address to receive the messages sent from the contact form.', 'yith-woocommerce-tab-manager' ),
		),
		'contact-colors-default'  => array(
			'id'           => 'ywtm_contact_colors_default',
			'name'         => __( 'Colors - Default state', 'yith-woocommerce-tab-manager' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					array(
						'id'      => 'background',
						'name'    => __( 'Background', 'yith-woocommerce-tab-manager' ),
						'default' => '#ffffff',
					),
					array(
						'id'      => 'border',
						'name'    => __( 'Borders', 'yith-woocommerce-tab-manager' ),
						'default' => '#c7c7c7',
					),
				),
				array(
					array(
						'id'      => 'form_bg',
						'name'    => __( 'Contact form background', 'yith-woocommerce-tab-manager' ),
						'default' => '#f9f9f9',
					),
				),
			),
		),
		'contact-colors-active'   => array(
			'id'           => 'ywtm_contact_colors_active',
			'name'         => __( 'Colors - Active state', 'yith-woocommerce-tab-manager' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					array(
						'id'      => 'background',
						'name'    => __( 'Background', 'yith-woocommerce-tab-manager' ),
						'default' => '#EEF5FC',
					),
					array(
						'id'      => 'border',
						'name'    => __( 'Borders', 'yith-woocommerce-tab-manager' ),
						'default' => '#AECCDD',
					),
				),
			),
		),
		'contact-section-end'     => array(
			'type' => 'sectionend',
		),
		'download-section-start'  => array(
			'type' => 'title',
			'name' => __( 'Download tab', 'yith-woocommerce-tab-manager' ),
		),
		'download-colors-default' => array(
			'id'           => 'ywtm_download_colors_default',
			'name'         => __( 'Colors - Default state', 'yith-woocommerce-tab-manager' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					array(
						'id'      => 'background',
						'name'    => __( 'Background', 'yith-woocommerce-tab-manager' ),
						'default' => '#F8F8F8',
					),
					array(
						'id'      => 'border',
						'name'    => __( 'Border', 'yith-woocommerce-tab-manager' ),
						'default' => '#c7c7c7',
					),
				),
				array(
					array(
						'id'      => 'background_btn',
						'name'    => __( 'Button Background', 'yith-woocommerce-tab-manager' ),
						'default' => '#ECECEC',
					),
					array(
						'id'      => 'border_btn',
						'name'    => __( 'Button Border', 'yith-woocommerce-tab-manager' ),
						'default' => '#C2C2C2',
					),
				),
				array(
					array(
						'id'      => 'button_text',
						'name'    => __( 'Button text', 'yith-woocommerce-tab-manager' ),
						'default' => '#4A5567',
					),
				),
			),
			'default'      => array(),
		),
		'download-colors-active'  => array(
			'id'           => 'ywtm_download_colors_active',
			'name'         => __( 'Colors - Hover state', 'yith-woocommerce-tab-manager' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					array(
						'id'      => 'background',
						'name'    => __( 'Background', 'yith-woocommerce-tab-manager' ),
						'default' => '#EEF5FC',
					),
					array(
						'id'      => 'border',
						'name'    => __( 'Border', 'yith-woocommerce-tab-manager' ),
						'default' => '#AECCDD',
					),
				),
				array(
					array(
						'id'      => 'background_btn',
						'name'    => __( 'Button Background', 'yith-woocommerce-tab-manager' ),
						'default' => '#DBEBFB',
					),
					array(
						'id'      => 'border_btn',
						'name'    => __( 'Button Border', 'yith-woocommerce-tab-manager' ),
						'default' => '#82B4D2',
					),
				),
				array(
					array(
						'id'      => 'button_text',
						'name'    => __( 'Button text', 'yith-woocommerce-tab-manager' ),
						'default' => '#3B7BB2',
					),
				),
			),
			'default'      => array(),
		),
		'download-section-end'    => array(
			'type' => 'sectionend',
		),
		'extra-section-start'     => array(
			'type' => 'title',
			'name' => __( 'Extra', 'yith-woocommerce-tab-manager' ),
		),
		'custom_css'              => array(
			'name'      => __( 'Custom Style', 'yith-woocommerce-tab-manager' ),
			'type'      => 'yith-field',
			'yith-type' => 'textarea',
			'id'        => 'ywtm_custom_style',
			'css'       => 'width:100%;min-height:100px;',
			'desc'      => __( 'Insert here your custom CSS', 'yith-woocommerce-tab-manager' ),
			'default'   => '',
		),
		'extra-section-end'       => array(
			'type' => 'sectionend',
		),

	),
);
