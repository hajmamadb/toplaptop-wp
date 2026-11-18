<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * This file manage all plugin premium functions
 *
 * @package YITH/TabManager/Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ywtm_map_old_icon_with_new' ) ) {
	/**
	 * Map the old icon with last font awesome
	 *
	 * @param string $icon_name The icon name.
	 *
	 * @return string
	 * @since 1.2.0
	 */
	function ywtm_map_old_icon_with_new( $icon_name ) {

		if ( false !== strpos( $icon_name, 'FontAwesome:fa-' ) ) {

			$icon_name = str_replace( 'FontAwesome:fa-', 'FontAwesome:', $icon_name );
		}

		return $icon_name;
	}
}

if ( ! function_exists( 'yith_tab_manager_get_layouts' ) ) {

	/**
	 * Get all tab layout
	 *
	 * @return array
	 */
	function yith_tab_manager_get_layouts() {
		return array(
			'default'  => __( 'Default', 'yith-woocommerce-tab-manager' ),
			'video'    => __( 'Video Gallery', 'yith-woocommerce-tab-manager' ),
			'gallery'  => __( 'Image Gallery', 'yith-woocommerce-tab-manager' ),
			'faq'      => __( 'FAQ', 'yith-woocommerce-tab-manager' ),
			'download' => __( 'Download', 'yith-woocommerce-tab-manager' ),
			'map'      => __( 'Map', 'yith-woocommerce-tab-manager' ),
			'contact'  => __( 'Contact form', 'yith-woocommerce-tab-manager' ),
		);
	}
}

if ( ! function_exists( 'yith_tab_manager_generate_css_var' ) ) {

	/**
	 * Generate the css vars for the custom style
	 *
	 * @return string
	 */
	function yith_tab_manager_generate_css_var() {

		$vars            = ":root{\n";
		$downloads       = get_option(
			'ywtm_download_colors_default',
			array(
				'background'     => '#F8F8F8',
				'border'         => '#c7c7c7',
				'background_btn' => '#ECECEC',
				'border_btn'     => '#C2C2C2',
				'button_text'    => '#4A5567',
			)
		);
		$downloads_hover = get_option(
			'ywtm_download_colors_active',
			array(
				'background'     => '#EEF5FC',
				'border'         => '#AECCDD',
				'background_btn' => '#DBEBFB',
				'border_btn'     => '#82B4D2',
				'button_text'    => '#3B7BB2',
			)
		);

		foreach ( $downloads as $property => $value ) {
			$property = str_replace( '_', '-', $property );
			$vars     .= "--ywtm-download-{$property}:{$value};\n";
		}

		foreach ( $downloads_hover as $property => $value ) {
			$property = str_replace( '_', '-', $property );
			$vars     .= "--ywtm-download-hover-{$property}:{$value};\n";
		}

		$faq_icon_position = get_option( 'ywtm_faq_icon_position', 'left' );

		if ( 'left' === $faq_icon_position ) {
			$faq_icon_position = 0;
			$flex              = 'auto';
		} else {
			$faq_icon_position = 1;
			$flex              = 1;
		}
		$faqs = get_option(
			'ywtm_faq_colors_default',
			array(
				'background' => '#f8f8f8',
				'text'       => '#4a5567',
				'border'     => '#c7c7c7',
			)
		);

		$faqs_active = get_option(
			'ywtm_faq_colors_active',
			array(
				'background'  => '#EEF5FC',
				'text'        => '#2c78a3',
				'border'      => '#aeccdd',
				'answer_text' => '#4A5567',
			)
		);

		$vars .= "--ywtm-faq-icon-position:{$faq_icon_position};\n";
		$vars .= "--ywtm-faq-flex:{$flex};\n";

		foreach ( $faqs as $property => $value ) {
			$property = str_replace( '_', '-', $property );
			$vars     .= "--ywtm-faq-{$property}:{$value};\n";
		}

		foreach ( $faqs_active as $property => $value ) {
			$property = str_replace( '_', '-', $property );
			$vars     .= "--ywtm-faq-open-{$property}:{$value};\n";
		}

		$contact_form = get_option(
			'ywtm_contact_colors_default',
			array(
				'background' => '#ffffff',
				'border'     => '#c7c7c7',
				'form_bg'    => '#f9f9f9',
			)
		);

		$contact_form_active = get_option(
			'ywtm_contact_colors_active',
			array(
				'background' => '#EEF5FC',
				'border'     => '#AECCDD',
			)
		);

		foreach ( $contact_form as $property => $value ) {
			$property = str_replace( '_', '-', $property );
			$vars     .= "--ywtm-form-{$property}:{$value};\n";
		}
		foreach ( $contact_form_active as $property => $value ) {
			$property = str_replace( '_', '-', $property );
			$vars     .= "--ywtm-form-active-{$property}:{$value};\n";
		}

		$vars .= '}';

		return $vars;
	}
}

if ( ! function_exists( 'yith_tab_manager_get_default_tab' ) ) {

	/**
	 * Return for the product the default tab
	 *
	 * @param WC_Product $product The product.
	 *
	 * @return array
	 */
	function yith_tab_manager_get_default_tab( $product ) {
		$content = $product ? $product->get_description() : '';

		return array(
			'description'            => array(
				'title'    => __( 'Description', 'woocommerce' ),
				'disabled' => empty( $content ),
			),
			'additional_information' => array(
				'title'    => __( 'Additional Information', 'woocommerce' ),
				'disabled' => $product && ! ( $product->has_attributes() || apply_filters( 'wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions() ) ),
			),
			'reviews'                => array(
				'title'    => __( 'Reviews (%d)', 'woocommerce' ),
				// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
				'disabled' => ! $product->get_reviews_allowed(),
			),
		);
	}
}