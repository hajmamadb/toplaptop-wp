<?php
/**
 * This class manage the metabox layout and the tab list table
 *
 * @package YITH/TabManager/classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'YITH_Tab_Manager_Post_Type_Admin_Premium' ) ) {
	/**
	 * The premium post type class
	 */
	class YITH_Tab_Manager_Post_Type_Admin_Premium extends YITH_Tab_Manager_Post_Type_Admin {

		/**
		 * The post type.
		 *
		 * @var string
		 */
		protected $post_type = 'ywtm_tab';


		/**
		 * The object to be shown for each row.
		 *
		 * @var YITH_Tab_Manager_Obj|null
		 */
		protected $object = null;

		/**
		 * Define the columns
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 */
		public function define_columns( $columns ) {
			if ( isset( $columns['date'] ) ) {
				unset( $columns['date'] );
			}

			if ( isset( $columns['title'] ) ) {
				unset( $columns['title'] );
			}
			$custom_columns = array(
				'custom_title' => __( 'Tab name', 'yith-woocommerce-tab-manager' ),
				'show_in'      => __( 'Show in', 'yith-woocommerce-tab-manager' ),
				'hide_in'      => __( 'Hide in', 'yith-woocommerce-tab-manager' ),
				'content_type' => __( 'Content type', 'yith-woocommerce-tab-manager' ),
				'enable'       => __( 'Enable', 'yith-woocommerce-tab-manager' ),
				'actions'      => __( 'Actions', 'yith-woocommerce-tab-manager' ),
			);

			return array_merge( $columns, $custom_columns );
		}


		/**
		 * Show the info where the tab is show
		 *
		 * @return void
		 */
		public function render_show_in_column() {
			switch ( $this->object->get_type() ) {

				case 'category':
					$list = $this->get_category_list( $this->object->get_categories() );
					break;
				case 'product':
					$list = $this->get_product_list( $this->object->get_products() );
					break;
				default:
					$list = __( 'All products', 'yith-woocommerce-tab-manager' );
					break;
			}
			echo wp_kses_post( $list );
		}

		/**
		 * Show where the tab is not visible
		 *
		 * @return void
		 */
		public function render_hide_in_column() {
			$can_exclude = yith_plugin_fw_is_true( $this->object->get_enable_exclude() );
			$list        = '-';
			if ( $can_exclude ) {
				switch ( $this->object->get_exclude_in() ) {
					case 'category':
						$list = $this->get_category_list( $this->object->get_categories_excluded() );
						break;
					case 'product':
						$list = $this->get_product_list( $this->object->get_products_excluded() );
						break;
				}
			}
			echo wp_kses_post( $list );
		}

		/**
		 * Show the content type for the tab
		 *
		 * @return void
		 */
		public function render_content_type_column() {
			$wc_tabs_opt = get_option( 'yith_tab_manager_wc_added', array() );
			$is_woo_tab  = in_array( $this->object->get_id(), $wc_tabs_opt, true );
			if ( ! $is_woo_tab ) {
				$layouts = yith_tab_manager_get_layouts();
				$layout  = isset( $layouts[ $this->object->get_layout() ] ) ? $layouts[ $this->object->get_layout() ] : '';
				echo wp_kses_post( $layout );
			} else {
				esc_html_e( 'Default WooCommerce content', 'yith-woocommerce-tab-manager' );
			}
		}


		/**
		 * Sync and trigger other event when a tab is created/updated
		 *
		 * @param int $tab_id The tab.
		 *
		 * @override
		 * @return void
		 */
		public function save_tab( $tab_id ) {
			remove_action( "save_post_{$this->post_type}", array( $this, 'save_tab' ), 20 );
			$this->sync_post_title( $tab_id );
			$this->sync_post_excerpt( $tab_id );
			$this->sync_lookup_tables( $tab_id );
			add_action( "save_post_{$this->post_type}", array( $this, 'save_tab' ), 20 );
		}

		/**
		 * Synchronize the excerpt
		 *
		 * @param int $tab_id The tab id.
		 *
		 * @return void
		 */
		public function sync_post_excerpt( $tab_id ) {
			remove_action( "save_post_{$this->post_type}", array( $this, 'sync_post_excerpt' ), 25 );
			$tab_desc = isset( $_REQUEST['yit_metaboxes']['_ywtm_tab_excerpt'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['yit_metaboxes']['_ywtm_tab_excerpt'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $tab_desc ) ) {
				$post_data = array(
					'post_excerpt' => $tab_desc,
				);
				wp_update_post( array_merge( array( 'ID' => $tab_id ), $post_data ) );
			}
			add_action( "save_post_{$this->post_type}", array( $this, 'sync_post_excerpt' ), 25 );
		}

		/**
		 * Sync the lookup tables
		 *
		 * @param int $tab_id The tab id.
		 *
		 * @return void
		 */
		public function sync_lookup_tables( $tab_id ) {
			try {
				/**
				 * The data store
				 *
				 * @var YITH_Tab_Manager_Data_Store $data_store
				 */
				$data_store = WC_Data_Store::load( 'ywtm-data-store' );
				$tab        = ywtm_get_tab( $tab_id );
				$data_store->delete_in_lookup( $tab );
				$data_store->add_in_lookup( $tab );
			} catch ( Exception $e ) {
				return;
			}
		}


		/**
		 * Add custom class in the row table
		 *
		 * @param array $classes The classes.
		 * @param array $css_class Extra css.
		 * @param int   $post_id The post id.
		 *
		 * @return array
		 */
		public function filter_post_class( $classes, $css_class, $post_id ) {

			if ( get_post_type( $post_id ) === $this->post_type ) {
				$tab = ywtm_get_tab( $post_id );
				if ( 'no' === $tab->get_is_editable() ) {
					$classes[] = 'ywtm-not-editable';
				}
			}

			return $classes;
		}


		/**
		 * Return the formatted category list
		 *
		 * @param array $category_ids The categories' ids to format.
		 * @param int   $max How many categories show.
		 *
		 * @return string
		 * @since 2.0.0
		 */
		private function get_category_list( $category_ids, $max = 3 ) {
			$categories_name = '';
			if ( ! empty( $category_ids ) ) {
				if ( ! is_array( $category_ids ) ) {
					$category_ids = explode( ',', $category_ids );
				}
				$total        = count( $category_ids );
				$category_ids = array_slice( $category_ids, 0, $max );
				$categories   = array();
				foreach ( $category_ids as $cat_id ) {
					$category     = get_term_by( 'id', $cat_id, 'product_cat', 'ARRAY_A' );
					$categories[] = $category['name'];
				}

				$categories_name = implode( ', ', $categories );
				if ( $total > $max ) {
					// translators: %1$ is a list of product , categories, %2$d is how many remains.
					$categories_name = sprintf( _x( '%1$s and %2$d more', 'Clothing,Accessories,Music and 2 more', 'yith-woocommerce-tab-manager' ), $categories_name, $total - $max );
				}
			}

			return $categories_name;
		}

		/**
		 * Formats the product list for the column
		 *
		 * @param array $product_ids The products ids to format.
		 * @param int   $max How many products show.
		 *
		 * @return string
		 */
		private function get_product_list( $product_ids, $max = 3 ) {
			$product_names = '';
			if ( ! empty( $product_ids ) ) {
				$_product_names = array();
				if ( ! is_array( $product_ids ) ) {
					$product_ids = explode( ',', $product_ids );
				}
				$total       = count( $product_ids );
				$product_ids = array_slice( $product_ids, 0, $max );
				foreach ( $product_ids as $product_id ) {
					$_product = wc_get_product( $product_id );
					if ( $_product ) {
						$_product_names[] = $_product->get_name();
					}
				}
				$product_names = implode( ', ', $_product_names );

				if ( $total > $max ) {
					// translators: %1$ is a list of product , categories, %2$d is how many remains.
					$product_names = sprintf( _x( '%1$s and %2$d more', 'Clothing,Accessories,Music and 2 more', 'yith-woocommerce-tab-manager' ), $product_names, $total - $max );
				}
			}

			return $product_names;
		}


		/**
		 * Allow to set the object
		 *
		 * @param int $object_id The object id.
		 *
		 * @return void
		 */
		public function set_object( $object_id ) {

			if ( get_post_type( $object_id ) === $this->post_type ) {
				$this->object = ywtm_get_tab( $object_id );
			}
		}
	}


}
