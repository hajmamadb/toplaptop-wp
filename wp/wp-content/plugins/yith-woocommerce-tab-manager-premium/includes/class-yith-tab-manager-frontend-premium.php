<?php
/**
 * This class show the custom tab in the product page
 *
 * @package YITH/TabManager/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_Frontend_Premium' ) ) {

	/**
	 * The class that mange the feature on product page
	 */
	class YITH_Tab_Manager_Frontend_Premium extends YITH_Tab_Manager_Frontend {


		/**
		 * The construct
		 */
		protected function __construct() {
			parent::__construct();

			if ( wp_is_mobile() ) {
				$hide_wc_in_mobile     = get_option( 'ywtm_hide_wc_tab_mobile', 'no' );
				$hide_plugin_in_mobile = get_option( 'ywtm_hide_tab_mobile', 'no' );

				if ( 'yes' === $hide_wc_in_mobile ) {
					add_filter( 'woocommerce_product_tabs', '__return_empty_array' );
				}

				if ( 'yes' === $hide_plugin_in_mobile ) {
					remove_filter( 'woocommerce_product_tabs', array( $this, 'add_tabs_woocommerce' ), 20 );
				}
			}
		}

		/**
		 * Add the custom tab
		 *
		 * @param array $tabs The WooCommerce Tabs.
		 *
		 * @return array
		 */
		public function add_tabs_woocommerce( $tabs ) {

			if ( YITH_Tab_Manager_Install::update_in_progess() ) {
				return $tabs;
			}
			$wc_tabs_opt = get_option( 'yith_tab_manager_wc_added', array() );
			$wc_tabs     = ywtm_get_tabs(
				array(
					'origin'           => 'woocommerce',
					'suppress_filters' => true,
				)
			);

			foreach ( $wc_tabs as $wc_tab ) {

				$key = array_search( $wc_tab->get_id(), array_map( 'intval', $wc_tabs_opt ), true );
				if ( isset( $tabs[ $key ] ) ) {
					if ( 'yes' === $wc_tab->get_active() ) {
						$tabs[ $key ]['priority'] = $wc_tab->get_order();
					} else {
						unset( $tabs[ $key ] );
					}
				}
			}

			$tabs       = $this->filter_wc_tabs_for_product( $tabs );
			$custom_tab = $this->add_custom_tabs();

			return array_merge( $tabs, $custom_tab );
		}

		/**
		 * Customize the WC tabs by product
		 *
		 * @param array $tabs The tabs.
		 *
		 * @return array
		 */
		public function filter_wc_tabs_for_product( $tabs ) {
			global $product;

			foreach ( $tabs as $tab_key => $wc_tab ) {
				$override_meta_key = '_ywtm_override_' . $tab_key;
				$hide_meta_key     = '_ywtm_hide_' . $tab_key;
				$title_meta_key    = '_ywtm_title_tab_' . $tab_key;
				$content_meta_key  = '_ywtm_content_tab_' . $tab_key;

				if ( 'yes' === $product->get_meta( $hide_meta_key ) ) {
					unset( $tabs[ $tab_key ] );
				} elseif ( 'yes' === $product->get_meta( $override_meta_key ) ) {
					$tabs[ $tab_key ]['title'] = $product->get_meta( $title_meta_key );
					$content                   = $product->get_meta( $content_meta_key );
					if ( ! empty( $content ) ) {
						$tabs[ $tab_key ]['callback']            = array( $this, 'put_custom_wc_tabs' );
						$tabs[ $tab_key ]['ywtm_custom_content'] = $content;
					}
				}
			}

			return $tabs;
		}

		/**
		 * Add the global tabs
		 *
		 * @return array
		 * @throws Exception The exception.
		 */
		protected function add_custom_tabs() {
			global $product;
			$yith_tabs = ywtm_get_tabs(
				array(
					'active' => 'yes',
					'origin' => 'plugin',
					'limit'  => - 1,
				)
			);

			$wc_tabs = array();
			foreach ( $yith_tabs as $tab ) {
				$can_show    = $tab->can_show( $product );
				$product_tab = $tab;
				if ( 'product' === $tab->get_content_in() ) {

					$product_tabs = $product->get_meta( 'ywtm_product_tabs' );

					if ( isset( $product_tabs[ $tab->get_id() ]['value'] ) ) {
						$product_tab = clone $tab;
						$product_tab->set_id( 0 );
						$product_tab->set_props(
							$product_tabs[ $tab->get_id() ]['value']
						);
					} else {
						$can_show = false;
					}
				}
				if ( $can_show && ! $product_tab->is_empty() ) {
					$tab_key             = apply_filters( 'ywtm_tab_key', 'ywtm-' . sanitize_title( $tab->get_name() ) . '-' . $tab->get_id(), $tab, $tab->get_id() );
					$wc_tabs[ $tab_key ] = array(
						'title'    => $tab->get_formatted_name(),
						'priority' => $tab->get_order(),
						'callback' => array( $this, 'put_content_tabs' ),
						'yith_obj' => $product_tab,
					);

					add_filter(
						'woocommerce_product_' . $tab_key . '_tab_title',
						array(
							$this,
							'decode_html_tab',
						),
					);
				}
			}

			return $wc_tabs;
		}

		/**
		 * Add custom wc tab by product
		 *
		 * @param string $key The tab key.
		 * @param array  $tab The tab.
		 *
		 * @return void
		 */
		public function put_custom_wc_tabs( $key, $tab ) {

			if ( isset( $tab['ywtm_custom_content'] ) ) {
				echo $tab['ywtm_custom_content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Print icon tab
		 *
		 * @param string $title The tab title.
		 *
		 * @return string
		 * @since 1.1.0
		 */
		public function decode_html_tab( $title ) {
			return html_entity_decode( $title, ENT_QUOTES );
		}
	}
}
