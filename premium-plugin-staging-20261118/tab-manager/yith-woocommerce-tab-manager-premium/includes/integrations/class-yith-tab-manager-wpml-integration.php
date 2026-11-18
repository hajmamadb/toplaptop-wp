<?php
/**
 * This class manage the integration with WPML
 *
 * @package YITH\TabManager\Integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Tab_Manager_WPML_Integration' ) ) {
	/**
	 * This class manage the integration with WPML
	 */
	class YITH_Tab_Manager_WPML_Integration {
		use YITH_Tab_Manager_Trait_Singleton;


		/**
		 * The constructor
		 */
		protected function __construct() {
			$this->force_default_editor();
			add_filter( 'icl_make_duplicate', array( $this, 'icl_make_duplicate' ), 99, 4 );
		}

		/**
		 * Force WPML to use the default editor for tabs
		 *
		 * @return void
		 */
		public function force_default_editor() {
			global $sitepress;

			$tm_settings = $sitepress->get_setting( 'translation-management' );
			if ( ! isset( $tm_settings[ WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_FOR_POST_TYPE_USE_NATIVE ]['ywtm_tab'] ) || ! $tm_settings[ WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_FOR_POST_TYPE_USE_NATIVE ]['ywtm_tab'] ) {
				$tm_settings[ WPML_TM_Post_Edit_TM_Editor_Mode::TM_KEY_FOR_POST_TYPE_USE_NATIVE ]['ywtm_tab'] = true;
				$sitepress->set_setting( 'translation-management', $tm_settings, true );
				WPML_TM_Post_Edit_TM_Editor_Mode::delete_all_posts_option( 'ywtm_tab' );
			}
		}

		/**
		 * Set the right product categories and product ids in the duplicated tab
		 *
		 * @param int    $master_post_id The original post id.
		 * @param string $lang The language.
		 * @param array  $postarr The array data.
		 * @param int    $id The duplicated post id.
		 *
		 * @return void
		 */
		public function icl_make_duplicate( $master_post_id, $lang, $postarr, $id ) {

			if ( get_post_type( $master_post_id ) === 'ywtm_tab' ) {
				try {
					$original_tab      = ywtm_get_tab( $master_post_id );
					$duplicated_tab    = ywtm_get_tab( $id );
					$product_ids       = $original_tab->get_products();
					$product_excl_ids  = $original_tab->get_products_excluded();
					$category_ids      = $original_tab->get_categories();
					$category_excl_ids = $original_tab->get_categories_excluded();
					$duplicated_tab->set_products( $this->get_translated_product_ids( $product_ids, $lang ) );
					$duplicated_tab->set_products_excluded( $this->get_translated_product_ids( $product_excl_ids, $lang ) );

					$duplicated_tab->set_categories( $this->get_translated_taxonomy_ids( $category_ids, $lang ) );
					$duplicated_tab->set_categories_excluded( $this->get_translated_taxonomy_ids( $category_excl_ids, $lang ) );
					$duplicated_tab->save();
				} catch ( Exception $e ) {
					die( esc_html( $e->getMessage() ) );
				}
			}
		}

		/**
		 * Return an array with translated product ids
		 *
		 * @param int[]  $product_ids A product id array.
		 * @param string $lang The language.
		 *
		 * @return array
		 */
		public function get_translated_product_ids( $product_ids, $lang ) {
			$translated_product_ids = array();
			if ( count( $product_ids ) > 0 ) {
				foreach ( $product_ids as $product_id ) {
					$translated_id = yit_wpml_object_id( $product_id, 'product', false, $lang );
					if ( ! is_null( $translated_id ) ) {
						$translated_product_ids[] = $translated_id;
					}
				}
			}

			return $translated_product_ids;
		}

		/**
		 * Return an array with translated taxonomy ids
		 *
		 * @param int[]  $taxonomy_ids A taxonomy id array.
		 * @param string $lang The language.
		 * @param string $taxonomy The taxonomy name.
		 *
		 * @return array
		 */
		public function get_translated_taxonomy_ids( $taxonomy_ids, $lang, $taxonomy = 'product_cat' ) {
			$translated_tax_ids = array();
			if ( count( $taxonomy_ids ) > 0 ) {
				foreach ( $taxonomy_ids as $taxonomy_id ) {
					$translated_id = yit_wpml_object_id( $taxonomy_id, $taxonomy, false, $lang );
					if ( ! is_null( $translated_id ) ) {
						$translated_tax_ids[] = $translated_id;
					}
				}
			}

			return $translated_tax_ids;
		}
	}
}
