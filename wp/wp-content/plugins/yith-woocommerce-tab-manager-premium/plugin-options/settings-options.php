<?php
/**
 * The file that contain the plugin options
 *
 * @package YITH\TabManager\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$desc_api_key = sprintf( '%s <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#key" target="_blank">%s</a>', __( 'Insert a valid API KEY or', 'yith-woocommerce-tab-manager' ), __( 'Get a Key', 'yith-woocommerce-tab-manager' ) );

return array(
	'settings' => array(
		'settings-section-start' => array(
			'type' => 'title',
			'name' => __( 'Tabs in mobile devices', 'yith-woocommerce-tab-manager' ),
		),
		'hide-wc-tabs'           => array(
			'id'        => 'ywtm_hide_wc_tab_mobile',
			'name'      => __( 'Hide WooCommerce tabs', 'yith-woocommerce-tab-manager' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'desc'      => __( 'Enable to hide the WooCommerce tabs in mobile devices.', 'yith-woocommerce-tab-manager' ),
		),
		'hide-plugin-tabs'       => array(
			'id'        => 'ywtm_hide_tab_mobile',
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'name'      => __( 'Hide plugin\'s tabs', 'yith-woocommerce-tab-manager' ),
			'default'   => 'no',
			'desc'      => __( 'Enable to hide the tabs created with this plugin in mobile devices.', 'yith-woocommerce-tab-manager' ),
		),
		'settings-section-end'   => array(
			'type' => 'sectionend',
		),
		'map-tab-section-start'  => array(
			'type' => 'title',
			'name' => __( 'Map tab', 'yith-woocommerce-tab-manager' ),
		),
		'google_api_key'         => array(
			'name'      => __( 'Google API KEY', 'yith-woocommerce-tab-manager' ),
			'id'        => 'ywtm_google_api_key',
			'desc'      => $desc_api_key,
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => '',
		),
		'map-tab-section-end'    => array(
			'type' => 'sectionend',
		),
	),
);
