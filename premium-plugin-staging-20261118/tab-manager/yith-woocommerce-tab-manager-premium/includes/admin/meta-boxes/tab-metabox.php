<?php
/**
 * This is the tab metabox configuration
 *
 * @package YITH WooCommerce Tab Manager\Admin
 * @since 1.0.0
 */

$args = array(
	'label'    => __( 'Tab configuration', 'yith-woocommerce-tab-manager' ),
	'pages'    => 'ywtm_tab',
	'priority' => 'default',
	'context'  => 'normal',
	'class'    => yith_set_wrapper_class() . ' ywtm_metabox',
	'tabs'     => array(
		'settings' => array(
			'label'  => __( 'Settings', 'yith-woocommerce-tab-manager' ),
			'fields' => apply_filters(
				'ywtm_options_metabox',
				array(
					'general_section'                 => array(
						'type' => 'title',
						'desc' => __( 'Tab configuration', 'yith-woocommerce-tab-manager' ),
					),
					'ywtm_tab_title'                  => array(
						'label'    => __( 'Tab name', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Enter a name to identify this tab.', 'yith-woocommerce-tab-manager' ),
						'type'     => 'text',
						'required' => true,
					),
					'ywtm_tab_excerpt'                => array(
						'label' => __( 'Excerpt', 'yith-woocommerce-tab-manager' ),
						'desc'  => __( 'Set a description for this tab. This content is useful to search for the tab in the table.', 'yith-woocommerce-tab-manager' ),
						'type'  => 'textarea',
						'std'   => '',
					),
					'ywtm_show_tab'                   => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => 'yes',
					),
					'ywtm_tab_type'                   => array(
						'label'   => __( 'Shown in', 'yith-woocommerce-tab-manager' ),
						'type'    => 'select',
						'class'   => 'yith-plugin-fw-select wc-enhanced-select',
						'std'     => 'global',
						'options' => array(
							'global'   => __( 'All products', 'yith-woocommerce-tab-manager' ),
							'category' => __( 'Specific product categories', 'yith-woocommerce-tab-manager' ),
							'product'  => __( 'Specific products', 'yith-woocommerce-tab-manager' ),
						),
						'desc'    => __( 'Set where to show this tab.', 'yith-woocommerce-tab-manager' ),
					),
					'ywtm_tab_category'               => array(
						'label'    => __( 'Choose Product Category', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Choose the product categories in which you want to show the tab', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ajax-terms',
						'required' => true,
						'data'     => array(
							'taxonomy'    => 'product_cat',
							'placeholder' => __( 'Search for a category', 'yith-woocommerce-tab-manager' ),
							'ywtm-deps'   => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_tab_type',
										'value' => 'category',
									),
								)
							),
						),
						'multiple' => true,

					),
					'ywtm_tab_product'                => array(
						'label'    => __( 'Choose Product', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Choose the Products in which you want to show the tab', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ajax-products',
						'multiple' => true,
						'required' => true,
						'std'      => array(),
						'data'     => array(
							'placeholder' => __( 'Search for a product', 'yith-woocommerce-tab-manager' ),
							'show_id'     => true,
							'ywtm-deps'   => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_tab_type',
										'value' => 'product',
									),
								)
							),
						),
					),
					'ywtm_enable_exclude'             => array(
						'label' => __( 'Exclude', 'yith-woocommerce-tab-manager' ),
						'type'  => 'onoff',
						'std'   => 'no',
						'desc'  => __( 'Enable this option to exclude products or categories so this tab is not shown there.', 'yith-woocommerce-tab-manager' ),
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'      => '_ywtm_tab_type',
										'value'   => 'product',
										'compare' => '!==',
									),
								)
							),
						),
					),
					'ywtm_exclude_in'                 => array(
						'label'   => __( 'Exclude in', 'yith-woocommerce-tab-manager' ),
						'type'    => 'radio',
						'options' => array(
							'category' => __( 'Specific product categories', 'yith-woocommerce-tab-manager' ),
							'product'  => __( 'Specific products', 'yith-woocommerce-tab-manager' ),
						),
						'std'     => 'category',
						'data'    => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'      => '_ywtm_tab_type',
										'value'   => 'product',
										'compare' => '!==',
									),
									array(
										'id'    => '_ywtm_enable_exclude',
										'value' => 'yes',
									),
								)
							),
						),
					),
					'ywtm_product_cat_exc'            => array(
						'label'    => __( 'Select product categories to exclude', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Choose the product categories in which you don\'t want to show the tab.', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ajax-terms',
						'data'     => array(
							'taxonomy'    => 'product_cat',
							'placeholder' => __( 'Search for a category', 'yith-woocommerce-tab-manager' ),
							'ywtm-deps'   => wp_json_encode(
								array(
									array(
										'id'      => '_ywtm_tab_type',
										'value'   => 'product',
										'compare' => '!==',
									),
									array(
										'id'    => '_ywtm_enable_exclude',
										'value' => 'yes',
									),
									array(
										'id'    => '_ywtm_exclude_in',
										'value' => 'category',
									),
								)
							),
						),
						'multiple' => true,

					),
					'ywtm_product_exc'                => array(
						'label'    => __( 'Select products', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Choose the products in which you don\'t want to show the tab.', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ajax-products',
						'multiple' => true,
						'std'      => array(),
						'data'     => array(
							'placeholder' => __( 'Search for a product', 'yith-woocommerce-tab-manager' ),
							'show_id'     => true,
							'ywtm-deps'   => wp_json_encode(
								array(
									array(
										'id'      => '_ywtm_tab_type',
										'value'   => 'product',
										'compare' => '!==',
									),
									array(
										'id'    => '_ywtm_enable_exclude',
										'value' => 'yes',
									),
									array(
										'id'    => '_ywtm_exclude_in',
										'value' => 'product',
									),
								)
							),
						),
					),
					'ywtm_icon_tab'                   => array(
						'label'   => __( 'Icon Tab', 'yith-woocommerce-tab-manager' ),
						'type'    => 'ywtm-icon-list',
						'class'   => 'yith-plugin-fw-select',
						'options' => array(
							'select' => array(
								'icon'   => __( 'Theme Icon', 'yit' ),
								'custom' => __( 'Custom Icon', 'yit' ),
								'none'   => __( 'None', 'yit' ),
							),
							'icon'   => '',
						),
						'std'     => array(
							'select' => 'icon',
							'icon'   => 'FontAwesome:envelope-o',
							'custom' => '',
						),
						'desc'    => __( 'Select an icon to show in the tab header.', 'yith-woocommerce-tab-manager' ),
					),
					'general_section_end'             => array(
						'type' => 'sep',

					),
					'configuration_section'           => array(
						'type' => 'title',
						'desc' => __( 'Tab content', 'yith-woocommerce-tab-manager' ),
					),
					'ywtm_order_tab'                  => array(
						'label' => __('Tab order', 'yith-woocommerce-tab-manager'),
						'type'  => 'number',
						'std'   => ywtm_get_default_priority(),
					),
					'ywtm_layout_type'                => array(
						'label'   => __( 'Layout', 'yith-woocommerce-tab-manager' ),
						'type'    => 'select',
						'class'   => 'yith-plugin-fw-select wc-enhanced-select',
						'std'     => 'default',
						'options' => yith_tab_manager_get_layouts(),
						'desc'    => __( 'Set the content layout for this tab.', 'yith-woocommerce-tab-manager' ),
					),
					'ywtm_tab_additional_content'     => array(
						'label' => __( 'Additional content', 'yith-woocommerce-tab-manager' ),
						'type'  => 'ywtm-modal-editor',
						'std'   => '',
						'desc'  => __( 'Set additional content to show above the video(s).', 'yith-woocommerce-tab-manager' ),
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'      => '_ywtm_layout_type',
										'value'   => 'default',
										'compare' => '!==',
									),
								)
							),
						),
					),
					'ywtm_tab_content_in'             => array(
						'label'   => __( 'Set the tab content for', 'yith-woocommerce-tab-manager' ),
						'type'    => 'radio',
						'options' => array(
							'all'     => __( 'All products', 'yith-woocommerce-tab-manager' ),
							'product' => __( 'Customized in each product', 'yith-woocommerce-tab-manager' ),
						),
						'std'     => 'all',
						'desc'    => __( 'If products, will find the tab in the product data metabox', 'yith-woocommerce-tab-manager' ),
					),
					'ywtm_text_tab'                   => array(
						'label'    => __( 'Tab content', 'yith-woocommerce-tab-manager' ),
						'desc'     => __( 'Set the content for this tab.', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ywtm-modal-editor',
						'required' => true,
						'data'     => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'default',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
					),
					'ywtm_video'                      => array(
						'label' => __( 'Videos', 'yith-woocommerce-tab-manager' ),
						'type'  => 'ywtm-video',
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'video',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
					),
					'ywtm_video_rows'                 => array(
						'label' => __( 'Videos per row', 'yith-woocommerce-tab-manager' ),
						'type'  => 'number',
						'min'   => 1,
						'max'   => 4,
						'std'   => 2,
						'desc'  => __( 'Set how many videos to show in each row.', 'yith-woocommerce-tab-manager' ),
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'video',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
					),
					'ywtm_gallery'                    => array(
						'label'    => __( 'Images', 'yith-woocommerce-tab-manager' ),
						'type'     => 'ywtm-media-gallery',
						'required' => true,
						'std'      => '',
						'data'     => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'gallery',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),

					),
					'ywtm_gallery_columns'            => array(
						'label' => __( 'Images per row', 'yith-woocommerce-tab-manager' ),
						'type'  => 'number',
						'min'   => 1,
						'max'   => 4,
						'std'   => 2,
						'desc'  => __( 'Set how many images to show in each row.', 'yith-woocommerce-tab-manager' ),
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'gallery',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
					),
					'ywtm_google_map_overlay_address' => array(
						'label'    => __( 'Address', 'yith-woocommerce-tab-manager' ),
						'type'     => 'text',
						'std'      => '',
						'desc'     => __( 'Set the address (example: "1600 Amphitheatre Parkway, Mountain View, CA").', 'yith-woocommerce-tab-manager' ),
						'data'     => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'map',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
						'required' => true,
					),
					'ywtm_google_map_full_width'      => array(
						'label' => __( 'Full-width map', 'yith-woocommerce-tab-manager' ),
						'type'  => 'onoff',
						'std'   => 'no',
						'desc'  => __( 'Enable this option to show the map in full width. Disable it to set a custom width.', 'yith-woocommerce-tab-manager' ),
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'map',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
					),
					'ywtm_google_map_width'           => array(
						'label' => __( 'Width (px)', 'yith-woocommerce-tab-manager' ),
						'type'  => 'number',
						'min'   => 1,
						'std'   => 100,
						'desc'  => __( 'Set the width of the map.', 'yith-woocommerce-tab-manager' ),
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'map',
									),
									array(
										'id'    => '_ywtm_google_map_full_width',
										'value' => 'no',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),

					),
					'ywtm_google_map_height'          => array(
						'label' => __( 'Height (px)', 'yith-woocommerce-tab-manager' ),
						'type'  => 'number',
						'min'   => 1,
						'std'   => 100,
						'desc'  => __( 'Set the height of the map.', 'yith-woocommerce-tab-manager' ),
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'map',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),

					),
					'ywtm_google_map_overlay_zoom'    => array(
						'label' => __( 'Map zoom', 'yith-woocommerce-tab-manager' ),
						'type'  => 'number',
						'min'   => 0,
						'max'   => 19,
						'std'   => 15,
						'desc'  => __( 'Set the zoom (0-19).', 'yith-woocommerce-tab-manager' ),
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'map',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),

					),
					'ywtm_form_tab'                   => array(
						'label' => __( 'Contact form', 'yith-woocommerce-tab-manager' ),
						'type'  => 'ywtm-contact-form',
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'contact',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
					),
					'ywtm_faqs'                       => array(
						'label' => __( 'FAQs', 'yith-woocommerce-tab-manager' ),
						'type'  => 'ywtm-faq',
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'faq',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
					),
					'ywtm_download'                   => array(
						'label' => __( 'Download', 'yith-woocommerce-tab-manager' ),
						'type'  => 'ywtm-download',
						'data'  => array(
							'ywtm-deps' => wp_json_encode(
								array(
									array(
										'id'    => '_ywtm_layout_type',
										'value' => 'download',
									),
									array(
										'id'    => '_ywtm_tab_content_in',
										'value' => 'all',
									),
								)
							),
						),
					),
					'ywtm_is_editable'                => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => 'yes',
					),
					'ywtm_origin'                     => array(
						'label' => '',
						'type'  => 'hidden',
						'std'   => 'plugin',
					),
				)
			),

		),

	),

);

return $args;
